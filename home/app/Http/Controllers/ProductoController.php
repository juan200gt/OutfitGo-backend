<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Marca;
use App\Models\Color;
use App\Models\Talla;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        // 1. Buscador Global
        if ($busqueda = $request->input('q')) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'LIKE', "%{$busqueda}%")
                  ->orWhere('descripcion', 'LIKE', "%{$busqueda}%")
                  ->orWhereHas('marca', function($qMarca) use ($busqueda) {
                      $qMarca->where('nombre', 'LIKE', "%{$busqueda}%");
                  });
            });
        }

        // 2. Filtros
        if ($request->filled('publico')) {
            $query->where('publico', $request->publico); 
        }

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('talla')) {
            $tallas = explode(',', $request->talla);
            $query->whereHas('tallas', function($q) use ($tallas) {
                $q->whereIn('nombre', $tallas);
            });
        }

        if ($request->filled('color')) {
            $colores = explode(',', $request->color);
            $query->whereHas('colores', function($q) use ($colores) {
                $q->whereIn('nombre', $colores);
            });
        }

        if ($request->filled('precio_min')) {
            $query->where('precio', '>=', $request->precio_min);
        }

        if ($request->filled('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }

        // 3. Clonamos la query ANTES de paginar
        $facetQuery = clone $query;
        $idsProductosVivos = $facetQuery->select('productos.id');

        $categoriasDisponibles = Categoria::whereHas('productos', function($q) use ($idsProductosVivos) {
            $q->whereIn('productos.id', $idsProductosVivos);
        })->get(['id', 'nombre']);

        $marcasDisponibles = Marca::whereHas('productos', function($q) use ($idsProductosVivos) {
            $q->whereIn('productos.id', $idsProductosVivos);
        })->get(['id', 'nombre']);

        $coloresDisponibles = Color::whereHas('productos', function($q) use ($idsProductosVivos) {
            $q->whereIn('productos.id', $idsProductosVivos);
        })->get(['id', 'nombre']);

        $tallasDisponibles = Talla::whereHas('productos', function($q) use ($idsProductosVivos) {
            $q->whereIn('productos.id', $idsProductosVivos);
        })->get(['id', 'nombre']);

        // 4. Cargar relaciones y paginar
        $productos = $query->with(['marca', 'categoria', 'tallas', 'colores', 'imagenes'])
                           ->latest()
                           ->paginate(12);

        // 5. Estructurar respuesta con las facetas
        return response()->json([
            'current_page' => $productos->currentPage(),
            'data' => $productos->items(),
            'total' => $productos->total(),
            'filtros_disponibles' => [
                'categorias' => $categoriasDisponibles,
                'marcas' => $marcasDisponibles,
                'colores' => $coloresDisponibles,
                'tallas' => $tallasDisponibles,
            ]
        ]);
    }    public function show($slug)
    {
        $producto = Producto::where('slug', $slug)
            ->with(['marca', 'categoria', 'tallas', 'colores', 'imagenes', 'variantes.talla', 'variantes.color', 'resenas.user:id,name'])
            ->firstOrFail();

        return response()->json($producto);
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
            ]);
        }

        return response()->json([
            'labels' => $historial->map(fn($h) => $h->created_at->format('d/m')),
            'precios' => $historial->map(fn($h) => (float) $h->precio)
        ]);
    }

    // Obtiene una lista de productos recomendados (de la misma categoría o aleatorios si no hay suficientes) para mostrar en el detalle de producto.
    public function recomendados($id)
    {
        $producto = Producto::findOrFail($id);

        $recomendados = Producto::where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $producto->id)
            ->where('stock', '>', 0)
            ->with(['marca', 'categoria', 'tallas', 'colores', 'imagenes'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        if ($recomendados->count() < 4) {
            $otros = Producto::where('id', '!=', $producto->id)
                ->whereNotIn('id', $recomendados->pluck('id'))
                ->where('stock', '>', 0)
                ->with(['marca', 'categoria', 'tallas', 'colores', 'imagenes'])
                ->inRandomOrder()
                ->take(4 - $recomendados->count())
                ->get();
            $recomendados = $recomendados->concat($otros);
        }

        return response()->json($recomendados);
    }
}
