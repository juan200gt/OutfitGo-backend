<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Marca;      
use App\Models\Categoria;  
use App\Models\Talla;
use App\Models\Color;
use App\Models\Order;

class ProductoController extends Controller
{

    public function index()
    {
        // Traemos los productos ordenados por los más nuevos, de 10 en 10
        $productos = Producto::orderBy('id', 'asc')->paginate(10);
        

        // Se los mando a la vista
        return view('index', compact('productos'));    
    }
    

    public function create()
    {
        // 1. Traemos las listas de la base de datos
        $marcas = Marca::all();
        $categorias = Categoria::all();
        $tallas = Talla::all();
        $colores = Color::all();

        // 2. Se las mandamos a la vista 
        return view('create', compact('marcas', 'categorias', 'tallas', 'colores'));
    }

    public function store(Request $request)
    {
        // 1. Validamos que nos mandan todo lo obligatorio
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'publico' => 'required|in:hombre,mujer,infantil,unisex',
            'marca_id' => 'required|integer',
            'tallas' => 'nullable|array',
            'colores' => 'nullable|array',
            'categoria_id' => 'required|integer',
            'imagen' => 'nullable|image|max:2048',
            'galeria_nuevas.*' => 'nullable|image|max:2048'
        ]);

        // 2. Gestionamos la subida de la imagen principal
        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('productos', 'public');
        }

        // 3. Creamos el producto en la base de datos
        $producto = Producto::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre) . '-' . rand(1000, 9999),
            'precio' => $request->precio,
            'stock' => $request->stock,
            'publico' => $request->publico,
            'marca_id' => $request->marca_id,
            'categoria_id' => $request->categoria_id,
            'url_imagen_principal' => $rutaImagen,    
        ]);

        if ($request->hasFile('galeria_nuevas')) {
            $galeria = [];
            $nuevasFotos = $request->file('galeria_nuevas');
            
            $fotosAProcesar = array_slice($nuevasFotos, 0, 3);
            
            foreach ($fotosAProcesar as $foto) {
                $galeria[] = $foto->store('productos/galeria', 'public');
            }
            
            $producto->galeria = $galeria;
            $producto->save();
        }

        // 4. Sincronizamos las Tallas y Colores seleccionados
        if ($request->has('tallas')) {
            $producto->tallas()->sync($request->tallas);
        }
        if ($request->has('colores')) {
            $producto->colores()->sync($request->colores);
        }

        // 5. Guardamos las variantes de stock si existen
        $variantes = $request->input('variantes', []);
        foreach ($variantes as $tallaId => $coloresArray) {
            foreach ($coloresArray as $colorId => $stock) {
                if ($stock !== null && $stock !== '') {
                    $producto->variantes()->create([
                        'talla_id' => $tallaId,
                        'color_id' => $colorId,
                        'stock' => $stock
                    ]);
                }
            }
        }

        return redirect('/admin/productos')->with('success', '¡Producto creado con éxito!');    
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        // Buscamos el producto en la base de datos
        $producto = Producto::findOrFail($id);

        // 1. Traemos las listas de la base de datos
        $marcas = Marca::all();
        $categorias = Categoria::all();
        $tallas = Talla::all();
        $colores = Color::all();
        
        // Le mandamos el producto a la vista
        return view('edit', compact('producto', 'marcas', 'categorias', 'tallas', 'colores'));
    }

    public function update(Request $request, string $id)
    {
        // 1. Buscamos el producto
        $producto = Producto::findOrFail($id);

        // 2. Validamos los datos del formulario
        $datosActualizar = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'nombre_en' => 'nullable|string|max:255',
            'nombre_fr' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'descripcion_en' => 'nullable|string',
            'descripcion_fr' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'publico' => 'sometimes|in:hombre,mujer,infantil,unisex',
            'marca_id' => 'sometimes|integer|exists:marcas,id',
            'categoria_id' => 'sometimes|integer|exists:categorias,id',
            'imagen' => 'nullable|image|max:2048',
            'galeria_nuevas.*' => 'nullable|image|max:2048',
        ]);

        // Quitamos campos que se gestionan aparte
        unset($datosActualizar['imagen'], $datosActualizar['galeria_nuevas']);

        // 3. Si el usuario ha subido una imagen nueva lo guardamos
        if ($request->hasFile('imagen')) {
            if ($producto->url_imagen_principal) {
                Storage::disk('public')->delete($producto->url_imagen_principal);
            }
            $datosActualizar['url_imagen_principal'] = $request->file('imagen')->store('productos', 'public');
        }

        if ($request->hasFile('galeria_nuevas')) {
            $galeriaActual = $producto->galeria ?? [];
            
            $huecosLibres = 3 - count($galeriaActual);
            
            if ($huecosLibres > 0) {
                $nuevasFotos = $request->file('galeria_nuevas');
                $fotosAProcesar = array_slice($nuevasFotos, 0, $huecosLibres);
                
                foreach ($fotosAProcesar as $foto) {
                    $ruta = $foto->store('productos/galeria', 'public');
                    $galeriaActual[] = $ruta;
                }
                
                $datosActualizar['galeria'] = $galeriaActual;
            } else {
                return redirect()->back()->with('error', 'El producto ya tiene el máximo de 3 imágenes en la galería.');
            }
        }

        // 4. Actualizamos el producto con los datos validados
        $producto->update($datosActualizar);

        // 5. Sincronizamos las tallas y colores al editar
        $producto->tallas()->sync($request->input('tallas', []));
        $producto->colores()->sync($request->input('colores', []));

        // 6. Actualizamos las variantes
        $producto->variantes()->delete(); // Borramos las antiguas
        $variantes = $request->input('variantes', []);
        foreach ($variantes as $tallaId => $coloresArray) {
            foreach ($coloresArray as $colorId => $stock) {
                if ($stock !== null && $stock !== '') {
                    $producto->variantes()->create([
                        'talla_id' => $tallaId,
                        'color_id' => $colorId,
                        'stock' => $stock
                    ]);
                }
            }
        }

        // 7. Volvemos a la tabla
        return redirect('/admin/productos')->with('success', '¡Producto actualizado correctamente!');
    }

    public function destroy(string $id)
    {
        // 1. Buscamos el producto por su ID 
        $producto = Producto::findOrFail($id);

        // 2. Borramos la imagen física principal del disco duro
        if ($producto->url_imagen_principal) {
            Storage::disk('public')->delete($producto->url_imagen_principal);
        }

        if (is_array($producto->galeria)) {
            foreach ($producto->galeria as $rutaGaleria) {
                Storage::disk('public')->delete($rutaGaleria);
            }
        }

        // 3. Limpiamos las tablas intermedias
        $producto->tallas()->detach();
        $producto->colores()->detach();

        // 4. Lo eliminamos de la base de datos
        $producto->delete();

        // 5. Volvemos a la tabla con un mensaje verde
        return redirect('/admin/productos')->with('success', '¡Producto eliminado correctamente!');
    }

    public function eliminarImagen($id)
    {
        $producto = Producto::findOrFail($id);

        if ($producto->url_imagen_principal) {
            Storage::disk('public')->delete($producto->url_imagen_principal);
            
            $producto->url_imagen_principal = null;
            $producto->save();

            return redirect()->back()->with('success', '¡Imagen eliminada correctamente!');
        }

        return redirect()->back()->with('error', 'El producto no tiene ninguna imagen que eliminar.');
    }

    public function aprobarDevolucion($id)
    {
        $pedido = Order::with('orderItems')->findOrFail($id);

        if ($pedido->estado !== 'devolucion_solicitada') {
             return redirect()->back()->with('error', 'Este pedido no está pendiente de devolución.');
        }

        // 1. Cambiamos el estado final
        $pedido->estado = 'devuelto';
        $pedido->save();

        // 2. Devolvemos el stock a las variantes de producto (no al producto general)
        foreach ($pedido->orderItems as $item) {
            if ($item->producto_variante_id) {
                $variante = \App\Models\ProductoVariante::find($item->producto_variante_id);
                if ($variante) {
                    $variante->increment('stock', $item->cantidad);
                }
            }
        }

        return redirect()->back()->with('success', ' ¡Paquete recibido, devolución aprobada y stock restaurado!');
    }


}