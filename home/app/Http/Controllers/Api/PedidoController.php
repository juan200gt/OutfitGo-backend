<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Mail\RecomendacionProductoMail;
use Illuminate\Support\Facades\Mail;

class PedidoController extends Controller
{
    /**
     * Devuelve el historial de pedidos del usuario logueado.
     */
    public function misPedidos(Request $request)
    {
        $pedidos = Order::where('user_id', $request->user()->id)
                        ->with('orderItems.variante.producto')
                        ->latest()  
                        ->paginate(5);


        return response()->json([
            'message' => 'Historial de pedidos recuperado con éxito.',
            'pedidos' => $pedidos
        ], 200);
    }

    public function cancelarPedido(Request $request, $id)
    {
        $pedido = Order::where('id', $id)
                        ->where('user_id', $request->user()->id) 
                        ->first();

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.'
            ], 404);
        }

        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Este pedido ya ha sido enviado y no se puede cancelar.'
            ], 400);
        }

        $pedido->estado = 'cancelado';
        $pedido->save();

        return response()->json([
            'message' => 'Pedido cancelado correctamente.',
            'pedido' => $pedido
        ], 200);
    }

    public function devolverPedido(Request $request, $id)
    {
        $pedido = Order::where('id', $id)
                        ->where('user_id', $request->user()->id) 
                        ->first();

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.'
            ], 404);
        }

        if ($pedido->estado !== 'enviado') {
            return response()->json([
                'message' => 'Solo se pueden devolver pedidos que ya han sido enviados.'
            ], 400);
        }

        $pedido->estado = 'devolucion_solicitada';
        $pedido->save();

        return response()->json([
            'message' => 'Devolución solicitada correctamente.',
            'pedido' => $pedido
        ], 200);
    }


public function enviarRecomendacion($userId)
{
    $ultimaOrden = Order::with('orderItems.producto')
        ->where('user_id', $userId)
        ->latest()
        ->first();

    if (!$ultimaOrden || $ultimaOrden->orderItems->isEmpty()) {
        return "El usuario no tiene compras.";
    }

    $categoriaId = $ultimaOrden->orderItems->first()->producto->categoria_id;
    $productoCompradoId = $ultimaOrden->orderItems->first()->producto_id;

    $recomendado = Producto::where('categoria_id', $categoriaId)
        ->where('id', '!=', $productoCompradoId)
        ->inRandomOrder() 
        ->first();

    if ($recomendado) {
        $user = $ultimaOrden->user; 
        Mail::to($user->email)->send(new RecomendacionProductoMail($user, $recomendado));
        
        return "Correo enviado con éxito";
    }

    return "No se encontró un producto similar para recomendar.";
}
}