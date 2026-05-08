<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Crear Nuevo Producto</h1>
            <a href="/admin/productos" class="text-gray-500 hover:underline">Volver a la lista</a>
        </div>

        <form action="{{ route('admin.productos.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf

            <div>
                <label class="block text-gray-700 font-bold mb-2">Nombre de la prenda</label>
                <input type="text" name="nombre" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: Zapatillas Nike Air" required>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Precio (€)</label>
                    <input type="number" step="0.01" name="precio" class="w-full border p-2 rounded" placeholder="49.99" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Marca</label>
                    <select name="marca_id" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Selecciona una marca...</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Categoría</label>
                    <select name="categoria_id" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Selecciona una categoría...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
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

                <div id="hidden-sync-inputs" style="display: none;">
                    @foreach($tallas as $talla)
                        <input type="checkbox" name="tallas[]" value="{{ $talla->id }}" id="sync_talla_{{ $talla->id }}">
                    @endforeach
                    @foreach($colores as $color)
                        <input type="checkbox" name="colores[]" value="{{ $color->id }}" id="sync_color_{{ $color->id }}">
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
                                    <tr id="row_{{ $talla->id }}_{{ $color->id }}" class="variant-row" style="display: none;" data-talla="{{ $talla->id }}" data-color="{{ $color->id }}">
                                        <td class="p-2 border text-sm">{{ $talla->nombre }}</td>
                                        <td class="p-2 border text-sm">{{ $color->nombre }}</td>
                                        <td class="p-2 border">
                                            <input type="number" min="0" name="variantes[{{ $talla->id }}][{{ $color->id }}]" id="stock_{{ $talla->id }}_{{ $color->id }}" value="0" class="w-full border p-1 rounded focus:ring-blue-500 focus:border-blue-500" disabled required>
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
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">
                    Guardar Producto
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
    </script>
</body>
</html>