<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use \App\Models\Producto;

class OutfitController extends Controller
{
    public function generarImagenOutfit(Request $request)
    {
        // 1. Validar la entrada (Angular / Postman)
        $ids = $request->input('product_ids');
        $urlModelo = $request->input('modelo_url');

        if (!$ids || !is_array($ids)) {
            return response()->json(['error' => 'Faltan las prendas de ropa (product_ids).'], 400);
        }
        if (!$urlModelo) {
            return response()->json(['error' => 'Falta la URL de la imagen del modelo (modelo_url).'], 400);
        }

        // Buscamos los productos en la base de datos
        $productos = Producto::whereIn('id', $ids)->get();

        if ($productos->isEmpty()) {
            return response()->json(['error' => 'No se encontraron los productos seleccionados'], 404);
        }

        try {
            // 2. Preparamos las partes (Prompt + Imágenes en Base64)
            // Instrucción clara y concisa para Nano Banana 2
            $partes = [
                ["text" => "Actúa como un probador virtual (Virtual Try-On). Mantén a la persona de la última imagen con su misma postura y rostro. Vístela usando de forma realista las prendas de ropa mostradas en las imágenes anteriores. Ajusta la iluminación y las texturas para que el resultado sea fotográfico."]
            ];

            // Añadimos cada prenda al array de partes
            foreach ($productos as $producto) {
                // Asegúrate de que $producto->url_imagen_principal es una URL válida y accesible
                $prendaBase64 = base64_encode(file_get_contents($producto->url_imagen_principal));
                $partes[] = [
                    "inline_data" => [
                        "mime_type" => "image/jpeg",
                        "data" => $prendaBase64
                    ]
                ];
            }

            // Finalmente, añadimos la imagen del modelo
            $modeloBase64 = base64_encode(file_get_contents($urlModelo));
            $partes[] = [
                "inline_data" => [
                    "mime_type" => "image/jpeg",
                    "data" => $modeloBase64
                ]
            ];

            // 3. Llamada a la API de Nano Banana 2 (Gemini 3.1 Flash Image)
            $modelName = "gemini-3.1-flash-image-preview";
            $apiKey = env('GEMINI_API_KEY');

            // Hacemos el POST (Le damos 120 segundos de timeout por si procesar la imagen tarda)
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(120) 
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}", [
                    "contents" => [
                        ["parts" => $partes]
                    ]
                    // OJO: No usamos generationConfig con response_mime_type porque da el error 400
                ]);

            // 4. Gestión de Errores de Google
            if ($response->failed()) {
                if ($response->status() === 429) {
                    return response()->json([
                        'error' => 'Límite de cuota excedido. Asegúrate de tener la facturación activa en Google Cloud para usar modelos Preview.',
                        'details' => $response->json()
                    ], 429);
                }
                return response()->json([
                    'error' => 'Error al comunicarse con Google AI Studio',
                    'details' => $response->json()
                ], $response->status());
            }

            $result = $response->json();

            // 5. Extraer la imagen generada (Base64)
            if (isset($result['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
                $base64Image = $result['candidates'][0]['content']['parts'][0]['inlineData']['data'];
                $mimeType = $result['candidates'][0]['content']['parts'][0]['inlineData']['mimeType'] ?? 'image/jpeg';
                
                return response()->json([
                    'outfit_url' => "data:{$mimeType};base64,{$base64Image}",
                    'explicacion' => 'Look completo generado con Gemini 3.1 Flash Image (Nano Banana 2).'
                ]);
            }

            return response()->json(['error' => 'La IA no devolvió el formato esperado', 'raw' => $result], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor', 'msg' => $e->getMessage()], 500);
        }
    }
}