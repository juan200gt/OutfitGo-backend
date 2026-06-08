<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Cargamos la variante y, a través de ella, su producto, talla y color
        $cartItems = CartItem::with(['variante.producto', 'variante.color', 'variante.talla'])
            ->where('user_id', $request->user()->id)
            ->get();

        return CartItemResource::collection($cartItems);
    }

    public function store(Request $request)
    {
        // 1. Ahora validamos que Angular nos mande talla y color
        $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'color' => ['required', 'string'],
            'talla' => ['required', 'string'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        // 2. Buscamos la variante exacta en la base de datos
        $variante = ProductoVariante::where('producto_id', $request->producto_id)
            ->whereHas('color', function($q) use ($request) {
                $q->where('nombre', $request->color);
            })
            ->whereHas('talla', function($q) use ($request) {
                $q->where('nombre', $request->talla);
            })
            ->first();

        if (!$variante) {
            return response()->json(['message' => 'Esta combinación de talla y color no existe.'], 404);
        }

        // 3. Comprobamos el stock DE LA VARIANTE
        if ($variante->stock < $request->cantidad) {
            return response()->json(['message' => 'No hay suficiente stock para esta talla y color.'], 422);
        }

        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('producto_variante_id', $variante->id)
            ->first();

        if ($cartItem) {
            $nuevaCantidad = $cartItem->cantidad + $request->cantidad;
            if ($variante->stock < $nuevaCantidad) {
                return response()->json(['message' => 'Stock insuficiente para añadir más.'], 422);
            }
            $cartItem->update(['cantidad' => $nuevaCantidad]);
        } else {
            $cartItem = CartItem::create([
                'user_id' => $request->user()->id,
                'producto_variante_id' => $variante->id,
                'cantidad' => $request->cantidad,
            ]);
        }

        $cartItem->load(['variante.producto', 'variante.color', 'variante.talla']);

        return response()->json([
            'message' => 'Producto añadido al carrito exitosamente',
            'item' => new CartItemResource($cartItem),
        ], 201);
    }

    /**
     * Actualizar la cantidad de un producto en el carrito.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem = CartItem::with('variante')->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $stockDisponible = $cartItem->variante ? $cartItem->variante->stock : ($cartItem->producto->stock ?? 0);

        if ($stockDisponible < $request->cantidad) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible para esta variante.',
            ], 422);
        }

        $cartItem->update(['cantidad' => $request->cantidad]);

        return response()->json([
            'message' => 'Cantidad actualizada exitosamente',
            'item' => new CartItemResource($cartItem),
        ]);
    }

    /**
     * Sincronizar el carrito de invitado con la base de datos al iniciar sesión.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.color' => ['required', 'string'],
            'items.*.talla' => ['required', 'string'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        foreach ($request->items as $item) {
            // Buscamos la variante exacta en la base de datos
            $variante = ProductoVariante::where('producto_id', $item['producto_id'])
                ->whereHas('color', function($q) use ($item) {
                    $q->where('nombre', $item['color']);
                })
                ->whereHas('talla', function($q) use ($item) {
                    $q->where('nombre', $item['talla']);
                })
                ->first();

            if (!$variante) {
                continue;
            }

            $cantidadAAgregar = $item['cantidad'];

            $cartItem = CartItem::where('user_id', $user->id)
                ->where('producto_variante_id', $variante->id)
                ->first();

            if ($cartItem) {
                $nuevaCantidad = $cartItem->cantidad + $cantidadAAgregar;
                if ($variante->stock < $nuevaCantidad) {
                    $nuevaCantidad = $variante->stock;
                }
                $cartItem->update(['cantidad' => $nuevaCantidad]);
            } else {
                if ($variante->stock < $cantidadAAgregar) {
                    $cantidadAAgregar = $variante->stock;
                }
                if ($cantidadAAgregar > 0) {
                    CartItem::create([
                        'user_id' => $user->id,
                        'producto_variante_id' => $variante->id,
                        'cantidad' => $cantidadAAgregar,
                    ]);
                }
            }
        }

        $cartItems = CartItem::with(['variante.producto', 'variante.color', 'variante.talla'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'message' => 'Carrito sincronizado exitosamente',
            'data' => CartItemResource::collection($cartItems),
        ]);
    }

    /**
     * Eliminar un item del carrito.
     */
    public function destroy(Request $request, $id)
    {
        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'message' => 'Producto eliminado del carrito exitosamente',
        ]);
    }
}
