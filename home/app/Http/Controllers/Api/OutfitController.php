<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Producto;


class OutfitController extends Controller
{
    public function generarImagenOutfit(Request $request)
    {
        $ids = $request->input('product_ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['error' => 'No has seleccionado ninguna prenda.'], 400);
        }

        $productos = Producto::whereIn('id', $ids)->get();

        try {
            $nombres = [];
            $imagenes = [];

            // 1. Procesamos cada prenda con tu lógica robusta
            foreach ($productos as $producto) {
                $url = $producto->url_imagen_principal;
                $archivo = null;
                $mime = 'image/jpeg';

                if (str_starts_with($url, 'http')) {
                    try {
                        $imgResponse = Http::get($url);
                        if ($imgResponse->successful()) {
                            $archivo = $imgResponse->body();
                            $mime = $imgResponse->header('Content-Type') ?? 'image/jpeg';
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error al descargar imagen externa: " . $url);
                    }
                } else {
                    $path = public_path($url);
                    if (file_exists($path)) {
                        $archivo = file_get_contents($path);
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $path);
                        finfo_close($finfo);
                    } else {
                        \Log::error("Archivo no encontrado: " . $path);
                    }
                }

                if ($archivo) {
                    $imagenes[] = "data:{$mime};base64," . base64_encode($archivo);
                }
                
                $nombres[] = $producto->nombre;
            }

            // 2. Preparamos el Prompt para Flux
            $descripcionPrendas = implode(', ', $nombres);
            $prompt = "Professional fashion photography editorial. A model wearing a complete outfit combining exactly these items: " . $descripcionPrendas . ". High-end studio lighting, 8k, photorealistic, 35mm lens.";

            // 3. Llamada a Flux 2 Pro
            $response = Http::withToken(env('REPLICATE_API_TOKEN'))
                ->withHeaders(['Prefer' => 'wait'])
                ->timeout(90)
                ->post('https://api.replicate.com/v1/models/black-forest-labs/flux-2-pro/predictions', [
                    'input' => [
                        'prompt' => $prompt,
                        'aspect_ratio' => '3:4',
                        'output_format' => 'jpg'
                        // Nota: En Flux 2 Pro estándar, le pasamos solo el prompt para evitar errores de validación. 
                        // El modelo dibujará el outfit basándose en los nombres de las prendas.
                    ]
                ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Error en la IA de Flux', 'details' => $response->json()], 500);
            }

            $resultado = $response->json();
            $output = $resultado['output'] ?? null;
            $imageUrl = is_array($output) ? $output[0] : $output;

            if (!$imageUrl) {
                return response()->json(['error' => 'La IA no devolvió la imagen final.', 'raw' => $resultado], 500);
            }

            return response()->json([
                'mensaje' => '¡Hecho! Aquí tienes el outfit de inspiración:',
                'outfit_url' => $imageUrl,
                'prendas_usadas' => $nombres
            ]);

        } catch (\Exception $e) {
            // Este es el error de PHP que verías en la pestaña Red
            return response()->json(['error' => 'Error interno de Laravel', 'msg' => $e->getMessage()], 500);
        }
    }
}