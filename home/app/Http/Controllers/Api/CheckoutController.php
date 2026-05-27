<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Mail\ConfirmacionCompra;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductoVariante;

class CheckoutController extends Controller
{
    public function iniciarPago(Request $request)
    {
        // 1. Validamos que Angular nos manda la dirección Y los productos
        $request->validate([
            'address_id' => 'required|integer',
            'productos' => 'required|array|min:1',
            // Aseguramos que cada producto traiga su ID de variante y la cantidad
            'productos.*.producto_variante_id' => 'required|integer', 
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->address_id);

        $total = 0;
        $orderItemsData = [];
        $line_items = [];

        // 2. Recorremos lo que manda Angular, pero BUSCAMOS EL PRECIO REAL en la BD por seguridad
        foreach ($request->productos as $item) {
            $variante = ProductoVariante::with(['producto', 'color', 'talla'])
                        ->find($item['producto_variante_id']);

            if (!$variante) {
                return response()->json(['message' => 'Error: Una de las prendas ya no está disponible.'], 422);
            }

            if ($variante->stock < $item['cantidad']) {
                return response()->json([
                    'message' => "Stock insuficiente para: {$variante->producto->nombre} (Talla: {$variante->talla->nombre})"
                ], 422);
            }

            // Calculamos el total con el precio real de la BD
            $total += $variante->producto->precio * $item['cantidad'];

            $orderItemsData[] = [
                'producto_id'          => $variante->producto_id,
                'producto_variante_id' => $variante->id,
                'cantidad'             => $item['cantidad'],
                'precio_unitario'      => $variante->producto->precio,
            ];

            $unit_amount = (int) round($variante->producto->precio * 100);
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "{$variante->producto->nombre} - {$variante->color->nombre} / {$variante->talla->nombre}"
                    ],
                    'unit_amount' => $unit_amount,
                ],
                'quantity' => $item['cantidad'],
            ];
        }

        try {
            DB::beginTransaction();

            // 3. Creamos el pedido
            $order = Order::create([
                'user_id'       => $user->id,
                'total'         => $total,
                'estado'        => 'pendiente',
                'nombre'        => $user->name,
                'apellidos'     => '', 
                'telefono'      => $address->telefono,
                'direccion'     => $address->direccion,
                'ciudad'        => $address->ciudad,
                'provincia'     => $address->provincia,
                'codigo_postal' => $address->codigo_postal,
                'notas'         => $request->notas ?? '',
            ]);

            // 4. Guardamos los items del pedido que preparamos antes
            foreach ($orderItemsData as $data) {
                $order->orderItems()->create($data);
            }

            // 5. Llamamos a Stripe
            Stripe::setApiKey(config('services.stripe.secret'));
            $frontend_url = env('FRONTEND_URL', 'https://outfitgo.duckdns.org');

            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'line_items'           => $line_items,
                'mode'                 => 'payment',
                'metadata'             => [
                    'order_id' => $order->id 
                ],
                'success_url'          => rtrim($frontend_url, '/') . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'           => rtrim($frontend_url, '/') . '/cart',
            ]);

            DB::commit();

            return response()->json(['url' => $checkout_session->url], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al preparar el pago: ' . $e->getMessage()], 500);
        }
    }

    public function validarCupon(Request $request)
    {
        $request->validate(['codigo' => 'required|string']);

        $cupon = \App\Models\Cupon::where('codigo', strtoupper($request->codigo))
            ->where('is_active', true)
            ->first();

        if (!$cupon) {
            return response()->json(['message' => 'Cupón no válido o caducado.'], 404);
        }

        return response()->json(['cupon' => $cupon], 200);
    }

    public function confirmarPago(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($request->session_id);

            if ($session->payment_status !== 'paid') {
                return response()->json(['message' => 'El pago no se ha completado.'], 400);
            }

            $orderId = $session->metadata->order_id ?? null;
            if (!$orderId) {
                return response()->json(['message' => 'La sesión de Stripe no contiene un pedido válido.'], 422);
            }

            $order = Order::with([
                'user',
                'orderItems.variante.producto',
                'orderItems.variante.color',
                'orderItems.variante.talla'
            ])->findOrFail($orderId);

            if ((int) $order->user_id !== (int) $request->user()->id) {
                return response()->json(['message' => 'No autorizado para confirmar este pedido.'], 403);
            }

            if ($order->estado === 'pagado') {
                return response()->json(['message' => 'Este pedido ya estaba confirmado.', 'order' => $order], 200);
            }

            DB::beginTransaction();

            foreach ($order->orderItems as $item) {
                if (!$item->variante) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'No se puede confirmar el pedido: una variante ya no existe.'
                    ], 422);
                }

                if ($item->variante->stock < $item->cantidad) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'No se puede confirmar el pedido: stock insuficiente en una variante.'
                    ], 422);
                }
            }

            $order->update(['estado' => 'pagado']);

            foreach ($order->orderItems as $item) {
                $item->variante->decrement('stock', $item->cantidad);
            }

            CartItem::where('user_id', $order->user_id)->delete();

            DB::commit();

            Mail::to($order->user->email)->send(new ConfirmacionCompra($order));

            // Si el usuario está suscrito a la newsletter, seleccionamos productos recomendados y le enviamos un correo personalizado.
            if ($order->user->newsletter) {
                // Evitamos recomendar productos que el usuario acaba de comprar en este pedido.
                $boughtProductIds = $order->orderItems->pluck('producto_id')->toArray();
                
                // Obtenemos hasta 3 productos aleatorios disponibles en stock para recomendar.
                $recomendaciones = \App\Models\Producto::where('stock', '>', 0)
                    ->whereNotIn('id', $boughtProductIds)
                    ->inRandomOrder()
                    ->take(3)
                    ->get();
                
                if ($recomendaciones->isNotEmpty()) {
                    $frontend_url = env('FRONTEND_URL', 'https://outfitgo.duckdns.org');
                    Mail::to($order->user->email)->send(new \App\Mail\RecomendacionNewsletterMail($order->user, $recomendaciones, $frontend_url));
                }
            }

            return response()->json([
                'message' => '¡Pago verificado y compra completada con éxito!',
                'order' => $order
            ], 200);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json(['message' => 'Error al verificar el pago: ' . $e->getMessage()], 500);
        }
    }
}