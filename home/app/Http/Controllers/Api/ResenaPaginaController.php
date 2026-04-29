<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResenaPagina;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResenaPaginaController extends Controller
{
    public function index(): JsonResponse
    {
        $resenas = ResenaPagina::with('user:id,name')
            ->where('visible_en_portada', true)
            ->latest()
            ->take(3)
            ->get();

        return response()->json($resenas, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|max:500',
        ]);

        $resena = new ResenaPagina();
        $resena->puntuacion = $request->puntuacion;
        $resena->comentario = $request->comentario;

        $resena->visible_en_portada = true;

        $resena->user_id = $request->user()->id;

        $resena->save();

        return response()->json([
            'mensaje' => 'Gracias por tu valoración. La revisaremos pronto.',
            'resena' => $resena
        ], 201);
    }
}