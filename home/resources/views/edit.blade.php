<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Editar: {{ $producto->nombre }}</h1>
            <a href="/admin/productos" class="text-gray-500 hover:underline">Cancelar y volver</a>
        </div>

        @if($producto->url_imagen_principal)
            <form id="form-borrar-imagen" action="{{ route('admin.productos.eliminarImagen', $producto->id) }}" method="POST" class="hidden">
                @csrf
            </form>
        @endif

        <form action="{{ route('admin.productos.update', $producto->id) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-700 font-bold mb-2">Nombre de la prenda</label>
                <input type="text" name="nombre" value="{{ $producto->nombre }}" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Precio (€)</label>
                    <input type="number" step="0.01" name="precio" value="{{ $producto->precio }}" class="w-full border p-2 rounded" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Stock Disponible</label>
                    <input type="number" name="stock" value="{{ $producto->stock }}" class="w-full border p-2 rounded" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Marca</label>
                    <select name="marca_id" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Selecciona una marca...</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}" {{ $producto->marca_id == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Categoría</label>
                    <select name="categoria_id" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Selecciona una categoría...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ $producto->categoria_id == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>  
            </div>
            <div class="grid grid-cols-2 gap-6 p-4 border rounded bg-gray-50">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Tallas Disponibles</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($tallas as $talla)
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" name="tallas[]" value="{{ $talla->id }}" 
                                    class="rounded text-blue-500" 
                                    {{ in_array($talla->id, $producto->tallas->pluck('id')->toArray()) ? 'checked' : '' }}>
                                <span>{{ $talla->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Colores</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($colores as $color)
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" name="colores[]" value="{{ $color->id }}" 
                                    class="rounded text-blue-500" 
                                    {{ in_array($color->id, $producto->colores->pluck('id')->toArray()) ? 'checked' : '' }}>
                                <span class="w-3 h-3 inline-block rounded-full border border-gray-300" style="background-color: {{ $color->hex_code }}"></span>
                                <span>{{ $color->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>  

            <div>
                <label class="block text-gray-700 font-bold mb-2">Público</label>
                <select name="publico" class="w-full border p-2 rounded">
                    <option value="unisex" {{ $producto->publico == 'unisex' ? 'selected' : '' }}>Unisex</option>
                    <option value="hombre" {{ $producto->publico == 'hombre' ? 'selected' : '' }}>Hombre</option>
                    <option value="mujer" {{ $producto->publico == 'mujer' ? 'selected' : '' }}>Mujer</option>
                    <option value="infantil" {{ $producto->publico == 'infantil' ? 'selected' : '' }}>Infantil</option>
                </select>
            </div>

            <div class="bg-gray-50 p-4 border rounded">
                <label class="block text-gray-700 font-bold mb-2">Foto Principal</label>
                            <div class="bg-gray-50 p-4 border rounded mt-4">
                <label class="block text-gray-700 font-bold mb-2">Galería de Imágenes (Máx 3)</label>
                
                @if(is_array($producto->galeria) && count($producto->galeria) > 0)
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Imágenes en la galería ({{ count($producto->galeria) }}/3):</p>
                        <div class="flex gap-4">
                            @foreach($producto->galeria as $index => $imagenGaleria)
                                <div class="relative">
                                    <img src="{{ str_starts_with($imagenGaleria, 'http') ? $imagenGaleria : asset('storage/' . $imagenGaleria) }}" alt="Galería" class="w-24 h-24 object-cover rounded shadow border border-gray-200">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if(!is_array($producto->galeria) || count($producto->galeria) < 3)
                    <input type="file" name="galeria_nuevas[]" multiple accept="image/*" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <p class="text-xs text-gray-500 mt-2">Puedes seleccionar varias fotos a la vez manteniendo pulsado Ctrl (Windows) o Cmd (Mac).</p>
                @else
                    <p class="text-sm text-red-500 font-bold mt-2">Has alcanzado el límite máximo de 3 imágenes.</p>
                @endif
            </div>
                @if($producto->url_imagen_principal)
                    <div class="mb-4 flex items-end gap-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Foto actual:</p>
                            <img src="{{ str_starts_with($producto->url_imagen_principal, 'http') ? $producto->url_imagen_principal : asset('storage/' . $producto->url_imagen_principal) }}" alt="Foto" class="w-32 h-32 object-cover rounded shadow border border-gray-200">
                        </div>
                        
                        <button type="submit" form="form-borrar-imagen" class="bg-red-100 text-red-600 px-3 py-2 rounded border border-red-200 hover:bg-red-200 text-sm font-semibold transition flex items-center gap-1" onclick="return confirm('¿Seguro que quieres quitar la foto de este producto?')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Borrar foto
                        </button>
                    </div>
                @endif
                
                <input type="file" name="imagen" accept="image/*" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <p class="text-xs text-gray-500 mt-2">Sube una nueva imagen para reemplazar la actual, o usa el botón de borrar para dejar el producto sin foto.</p>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded hover:bg-blue-600 transition shadow-sm">
                    Guardar Cambios
                </button>
            </div>
        </form>

    </div>

</body>
</html>