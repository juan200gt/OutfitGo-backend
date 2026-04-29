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

        // 2. Recogemos los datos del formulario (excluimos la galería para tratarla manual)
        $datosActualizar = $request->except(['imagen', '_token', '_method', 'tallas', 'colores', 'galeria_nuevas']);

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

        // 4. Actualizamos el producto con todos los datos
        $producto->update($datosActualizar);

        // 5. Sincronizamos las tallas y colores al editar
        $producto->tallas()->sync($request->input('tallas', []));
        $producto->colores()->sync($request->input('colores', []));

        // 6. Volvemos a la tabla
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

        // 2. Devolvemos el stock a los productos de la tienda
        foreach ($pedido->orderItems as $item) {
            $producto = Producto::find($item->producto_id);
            if ($producto) {
                $producto->increment('stock', $item->cantidad);
            }
        }

        return redirect()->back()->with('success', ' ¡Paquete recibido, devolución aprobada y stock restaurado!');
    }

    public function historialPrecios($id)
    {
        $producto = Producto::with(['historialPrecios' => function($query) {
            $query->orderBy('created_at', 'asc'); 
        }])->findOrFail($id);

        $historial = $producto->historialPrecios;

        if ($historial->isEmpty()) {
            return response()->json([
                'labels' => [now()->format('d/m')],
                'precios' => [(float) $producto->precio]
            ], 200);
        }

        return response()->json([
            'labels' => $historial->map(fn($h) => $h->created_at->format('d/m')),
            'precios' => $historial->map(fn($h) => (float) $h->precio)
        ], 200);
    }
}