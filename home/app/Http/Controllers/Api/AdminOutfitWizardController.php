<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log; 


class AdminOutfitWizardController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'user_prompt' => 'required|string|max:1000',
        ]);

        try {
            // Obtenemos 30 productos activos para tener variedad
            $productos = Producto::with(['categoria', 'marca', 'colores', 'tallas'])
                ->where('stock', '>', 0)
                ->inRandomOrder()
                ->take(30)
                ->get();

            $inventario = $productos->map(function($prod) {
                return [
                    'id' => $prod->id,
                    'nombre' => $prod->nombre,
                    'marca' => $prod->marca->nombre,
                    'categoria' => $prod->categoria->nombre,
                    'descripcion' => $prod->descripcion,
                    'colores_disponibles' => $prod->colores->pluck('nombre')->toArray(),
                    'tallas_en_stock' => $prod->tallas->pluck('nombre')->toArray(),
                    'precio' => $prod->precio . '€'
                ];
            });

            $systemPrompt = "Eres un personal shopper experto. Recibes el texto de un cliente y un catálogo en JSON con descripciones de productos. 

            TU MISIÓN:
            1. Analiza los deseos del cliente (estilo, ocasión).
            2. FILTRA ESTRICTAMENTE: Si el cliente menciona una talla, color o marca específica, SOLO puedes recomendar productos que coincidan exactamente con esos datos en el catálogo.
            3. Si el cliente pide algo que NO tienes en su talla o color, indícalo en la explicación.

            REGLA DE SEGURIDAD: Evalúa si el texto del cliente tiene que ver con ropa, moda, estilo, clima, eventos o compras. Si habla de política, conflictos u otros temas, devuelve este JSON:
            {\"productos_ids\": [], \"explicacion\": \"Lo siento, soy un asistente especializado en moda y estilo. No puedo ayudarte con ese tema.\"}

            Si el texto SÍ es sobre moda, tu trabajo es elegir los artículos que mejor encajen (máximo 5). Devuelve ÚNICAMENTE un JSON con esta estructura: 
            {\"productos_ids\": [id1, id2, id3, id4, id5], \"explicacion\": \"tu texto justificando por qué esas prendas encajan\"}.";

            $userPrompt = "El cliente dice: {$request->user_prompt}. Catálogo: " . json_encode($inventario);


            $motor = config('services.motor_ia', 'groq'); 
            $respuestaTexto = null;

            if ($motor === 'groq') {
                $groqApiKey = config('services.groq.api_key');

                if (!$groqApiKey) {
                    return response()->json(['error' => 'El servicio de IA no está configurado correctamente.'], 500);
                }

                $response = Http::timeout(15)
                    ->retry(3, 2000)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $groqApiKey,
                        'Content-Type' => 'application/json'
                    ])
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => 'llama-3.1-8b-instant', 
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userPrompt]
                        ],
                        'temperature' => 0.7,
                        'response_format' => ['type' => 'json_object']
                    ]);

                if ($response->failed()) {
                    Log::error('Error API Groq', ['body' => $response->body()]);
                    return response()->json(['error' => 'El servicio de IA no está disponible en este momento.'], 500);
                }

                $body = $response->json();
                $respuestaTexto = $body['choices'][0]['message']['content'] ?? null;

                // 4. PROCESAR LA RESPUESTA
                if (!$respuestaTexto) {
                    return response()->json(['error' => 'No se pudo obtener respuesta de la IA'], 500);
                }

                $content = json_decode($respuestaTexto, true);
                
                // 5. BUSCAR LOS PRODUCTOS RECOMENDADOS EN LA DB
                $productos_recomendados = Producto::with('categoria')
                    ->whereIn('id', $content['productos_ids'] ?? [])
                    ->get();

                return response()->json([
                    'productos' => $productos_recomendados,
                    'explicacion' => $content['explicacion'] ?? 'Aquí tienes una selección basada en tu estilo.',
                    'motor_usado' => $motor
                ]);

            } elseif ($motor === 'gemini') {
                $geminiApiKey = config('services.gemini.api_key');
            
                if (!$geminiApiKey) {
                    return response()->json(['error' => 'El servicio de IA no está configurado correctamente.'], 500);
                }

            // Llamada a la API de Gemini con la API key como cabecera en vez de query param
            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $geminiApiKey,
                ])
                ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent', [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $userPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if ($response->failed()) {
                Log::error('Error API Gemini', ['body' => $response->body()]);
                return response()->json(['error' => 'El servicio de IA no está disponible en este momento.'], 500);
            }

            $body = $response->json();

            $respuestaTexto = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$respuestaTexto) {
                return response()->json(['error' => 'Respuesta vacía del servicio de IA.'], 500);
            }

            $content = json_decode($respuestaTexto, true);

            if (!$content || !isset($content['productos_ids'])) {
                return response()->json(['error' => 'Formato de respuesta inválido del servicio de IA.'], 500);
            }

            $productos_recomendados = Producto::with('categoria')->whereIn('id', $content['productos_ids'])->get();

            return response()->json([
                'productos' => $productos_recomendados,
                'explicacion' => $content['explicacion'] ?? 'Recomendación generada por el Asistente de Outfits.'
            ]);
            } else {
                return response()->json(['error' => 'Motor de IA no configurado.'], 500);
            }



        } catch (\Exception $e) {
            Log::error("Outfit Wizard Falló | Error: {$e->getMessage()}", [
                'user_prompt' => $request->user_prompt
            ]);
            return response()->json([
                'error' => 'Hubo un problema conectándose al servicio del Personal Shopper. Nuestros técnicos han sido alertados.'
            ], 500);        
        }
    }
}
