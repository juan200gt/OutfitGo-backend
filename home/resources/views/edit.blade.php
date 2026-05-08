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

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Precio (€)</label>
                    <input type="number" step="0.01" name="precio" value="{{ $producto->precio }}" class="w-full border p-2 rounded" required>
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
            <!-- Se elimina el grid de checkboxes para usar el constructor de variantes más abajo -->  

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

            <div class="bg-gray-50 p-4 border rounded mt-4">
                <h3 class="text-gray-700 font-bold mb-2">Variantes (Tallas y Colores)</h3>
                <p class="text-xs text-gray-500 mb-4">Añade las combinaciones exactas de talla y color que vendes para este producto y establece su stock inicial.</p>
                
                <div class="flex flex-wrap md:flex-nowrap items-end gap-4 mb-4 bg-white p-4 border rounded shadow-sm">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Talla</label>
                        <select id="select-talla" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecciona Talla...</option>
                            @foreach($tallas as $talla)
                                <option value="{{ $talla->id }}">{{ $talla->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Color</label>
                        <select id="select-color" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecciona Color...</option>
                            @foreach($colores as $color)
                                <option value="{{ $color->id }}">{{ $color->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Stock</label>
                        <input type="number" id="input-stock" min="0" value="0" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <button type="button" id="btn-add-variant" class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                            + Añadir
                        </button>
                    </div>
                </div>

                <!-- Inputs ocultos para sync de tallas y colores -->
                <div id="hidden-sync-inputs" style="display: none;">
                    @foreach($tallas as $talla)
                        <input type="checkbox" name="tallas[]" value="{{ $talla->id }}" id="sync_talla_{{ $talla->id }}" {{ in_array($talla->id, $producto->tallas->pluck('id')->toArray()) ? 'checked' : '' }}>
                    @endforeach
                    @foreach($colores as $color)
                        <input type="checkbox" name="colores[]" value="{{ $color->id }}" id="sync_color_{{ $color->id }}" {{ in_array($color->id, $producto->colores->pluck('id')->toArray()) ? 'checked' : '' }}>
                    @endforeach
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse bg-white border" id="variants-table">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700">
                                <th class="p-2 border">Talla</th>
                                <th class="p-2 border">Color</th>
                                <th class="p-2 border w-1/3">Stock (Unidades)</th>
                                <th class="p-2 border text-center">Quitar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tallas as $talla)
                                @foreach($colores as $color)
                                    @php
                                        // Buscar si existe esta variante en BD
                                        $varianteDb = $producto->variantes->firstWhere(function($item) use ($talla, $color) {
                                            return $item->talla_id == $talla->id && $item->color_id == $color->id;
                                        });
                                        $isActive = $varianteDb ? true : false;
                                        $stock = $varianteDb ? $varianteDb->stock : 0;
                                    @endphp
                                    <tr id="row_{{ $talla->id }}_{{ $color->id }}" class="variant-row" style="{{ $isActive ? '' : 'display: none;' }}" data-talla="{{ $talla->id }}" data-color="{{ $color->id }}">
                                        <td class="p-2 border text-sm">{{ $talla->nombre }}</td>
                                        <td class="p-2 border text-sm">{{ $color->nombre }}</td>
                                        <td class="p-2 border">
                                            <input type="number" min="0" name="variantes[{{ $talla->id }}][{{ $color->id }}]" id="stock_{{ $talla->id }}_{{ $color->id }}" value="{{ $stock }}" class="w-full border p-1 rounded focus:ring-blue-500 focus:border-blue-500" {{ $isActive ? '' : 'disabled' }} required>
                                        </td>
                                        <td class="p-2 border text-center">
                                            <button type="button" class="text-red-500 hover:text-red-700 font-bold px-2 py-1 text-lg leading-none" onclick="quitarVariante({{ $talla->id }}, {{ $color->id }})">&times;</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded hover:bg-blue-600 transition shadow-sm">
                    Guardar Cambios
                </button>
            </div>
        </form>

    </div>

    <script>
        function actualizarSync() {
            // Desmarcamos todos los inputs ocultos
            document.querySelectorAll('#hidden-sync-inputs input[type="checkbox"]').forEach(cb => cb.checked = false);
            // Marcamos solo aquellos correspondientes a las variantes activas (visibles)
            document.querySelectorAll('.variant-row[style=""]').forEach(row => {
                document.getElementById('sync_talla_' + row.dataset.talla).checked = true;
                document.getElementById('sync_color_' + row.dataset.color).checked = true;
            });
        }

        function agregarVariante() {
            const tId = document.getElementById('select-talla').value;
            const cId = document.getElementById('select-color').value;
            const stock = document.getElementById('input-stock').value;

            if (!tId || !cId) return alert('Por favor, selecciona una talla y un color.');

            const row = document.getElementById(`row_${tId}_${cId}`);
            if (row.style.display === '') return alert('Esta combinación de talla y color ya ha sido añadida.');

            // Mostrar fila y habilitar input
            row.style.display = '';
            const input = document.getElementById(`stock_${tId}_${cId}`);
            input.disabled = false;
            input.value = stock;

            actualizarSync();

            // Resetear selects
            document.getElementById('select-talla').value = '';
            document.getElementById('select-color').value = '';
            document.getElementById('input-stock').value = '0';
        }

        function quitarVariante(tId, cId) {
            // Ocultar fila y deshabilitar input para que no se envíe al servidor
            const row = document.getElementById(`row_${tId}_${cId}`);
            row.style.display = 'none';
            document.getElementById(`stock_${tId}_${cId}`).disabled = true;

            actualizarSync();
        }

        // Hacer la función agregarVariante accesible al botón
        document.getElementById('btn-add-variant').addEventListener('click', agregarVariante);

        // Inicializar sync por si acaso
        document.addEventListener('DOMContentLoaded', actualizarSync);
    </script>
</body>
</html>