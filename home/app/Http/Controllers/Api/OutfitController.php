<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Producto;


class OutfitController extends Controller
{
    public function generarImagenOutfit(Request $request)
    {
        // Validación con validate() en lugar de manual
        $request->validate([
            'product_ids' => 'required|array|min:1|max:10',
            'product_ids.*' => 'required|integer|exists:productos,id',
        ]);

        $ids = $request->input('product_ids');
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
                        Log::error("Error al descargar imagen externa: " . $url);
                    }
                } else {
                    $path = public_path($url);
                    if (file_exists($path)) {
                        $archivo = file_get_contents($path);
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $path);
                        finfo_close($finfo);
                    } else {
                        Log::error("Archivo no encontrado: " . $path);
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
            $replicateToken = config('services.replicate.api_token');

            if (!$replicateToken) {
                return response()->json(['error' => 'El servicio de generación de imágenes no está configurado.'], 500);
            }

            $response = Http::withToken($replicateToken)
                ->withHeaders(['Prefer' => 'wait'])
                ->timeout(90)
                ->post('https://api.replicate.com/v1/models/black-forest-labs/flux-2-pro/predictions', [
                    'input' => [
                        'prompt' => $prompt,
                        'aspect_ratio' => '3:4',
                        'output_format' => 'jpg'
                    ]
                ]);

            if ($response->failed()) {
                Log::error('Error en la IA de Flux', ['body' => $response->body()]);
                return response()->json(['error' => 'El servicio de generación de imágenes no está disponible en este momento.'], 500);
            }

            $resultado = $response->json();
            $output = $resultado['output'] ?? null;
            $imageUrl = is_array($output) ? $output[0] : $output;

            if (!$imageUrl) {
                Log::error('Flux no devolvió imagen', ['raw' => $resultado]);
                return response()->json(['error' => 'No se pudo generar la imagen del outfit.'], 500);
            }

            return response()->json([
                'mensaje' => '¡Hecho! Aquí tienes el outfit de inspiración:',
                'outfit_url' => $imageUrl,
                'prendas_usadas' => $nombres
            ]);

        } catch (\Exception $e) {
            Log::error('Error interno en generarImagenOutfit', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'Error al generar el outfit. Inténtalo de nuevo más tarde.'], 500);
        }
    }
}