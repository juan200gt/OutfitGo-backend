<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel Admin - Productos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-md">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Gestión de Productos</h1>        
            

            
            <a href="{{ route('admin.productos.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-bold">
                + Nuevo Producto
            </a>            
            
            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.logout') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-bold text-sm shadow">
                    Cerrar Sesión
                </a>
            </div>
        </div>

        
        <!-- SECCIÓN DE PRUEBA TEMPORAL DE OUTFIT -->
 <!--        <div class="mb-6 p-4 border-2 border-dashed border-purple-400 bg-purple-50 rounded-lg">
            <h2 class="text-xl font-bold text-purple-800 mb-2">🧪 Prueba de IA - Generar Outfit</h2>
            <p class="text-sm text-purple-600 mb-4">Usa este panel para probar rápidamente la API de <code>OutfitController</code>. Ingresa los IDs de los productos separados por comas y mira la magia.</p>
            <form id="testOutfitForm" class="flex flex-wrap items-center gap-4">
                <input type="text" id="testOutfitIds" placeholder="Ej: 1, 2, 5" class="border border-purple-300 p-2 rounded w-64 focus:ring focus:ring-purple-200 outline-none">
                <button type="submit" id="btnTestOutfit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-bold transition-colors">Generar Outfit</button>
            </form>
            
            <div id="testOutfitResult" class="mt-4 hidden p-4 bg-white rounded shadow-inner">
                <div id="testOutfitLoading" class="flex items-center text-purple-600 font-semibold gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Generando imagen de outfit con IA... Esto puede tardar hasta 90 segundos.</span>
                </div>
                <div id="testOutfitError" class="text-red-500 font-bold hidden"></div>
                <div id="testOutfitSuccess" class="hidden flex flex-col items-center">
                    <p class="text-green-600 font-bold mb-2">¡Generado con éxito!</p>
                    <img id="testOutfitImg" src="" alt="Outfit Generado" class="max-w-xs rounded shadow border border-gray-200">
                </div>
            </div>
        </div>
        
        <script>
            document.getElementById('testOutfitForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const idsInput = document.getElementById('testOutfitIds').value;
                const ids = idsInput.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
                
                if(ids.length === 0) return alert('Por favor, ingresa al menos un ID válido.');
                
                const btn = document.getElementById('btnTestOutfit');
                const resultDiv = document.getElementById('testOutfitResult');
                const loadingDiv = document.getElementById('testOutfitLoading');
                const errorDiv = document.getElementById('testOutfitError');
                const successDiv = document.getElementById('testOutfitSuccess');
                const img = document.getElementById('testOutfitImg');
                
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                resultDiv.classList.remove('hidden');
                loadingDiv.classList.remove('hidden');
                errorDiv.classList.add('hidden');
                successDiv.classList.add('hidden');
                
                try {
                    const res = await fetch('/api/generar-outfit', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ product_ids: ids })
                    });
                    
                    const data = await res.json();
                    
                    if(res.ok) {
                        img.src = data.outfit_url;
                        loadingDiv.classList.add('hidden');
                        successDiv.classList.remove('hidden');
                    } else {
                        loadingDiv.classList.add('hidden');
                        errorDiv.textContent = 'Error: ' + (data.error || JSON.stringify(data));
                        errorDiv.classList.remove('hidden');
                    }
                } catch (error) {
                    loadingDiv.classList.add('hidden');
                    errorDiv.textContent = 'Error de red: ' + error.message;
                    errorDiv.classList.remove('hidden');
                } finally {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        </script> -->
        <!-- FIN SECCIÓN DE PRUEBA -->
        
        <style>
            @keyframes desaparecer {
                0%   { opacity: 1; max-height: 200px; }
                70%  { opacity: 1; max-height: 200px; } 
                90%  { opacity: 0; max-height: 200px; padding: 0.75rem 1rem; margin-bottom: 1rem; border-width: 1px; } 
                100% { opacity: 0; max-height: 0; padding: 0; margin-bottom: 0; border-width: 0; overflow: hidden; } 
            }
            .alerta-temporal {
                animation: desaparecer 4s forwards; 
                overflow: hidden; 
            }
        </style>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 alerta-temporal">
                {{ session('success') }}
            </div>
        @endif


        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="p-3 border-b">ID</th>
                    <th class="p-3 border-b">Imagen</th>
                    <th class="p-3 border-b">Nombre</th>
                    <th class="p-3 border-b">Precio</th>
                    <th class="p-3 border-b">Stock</th>
                    <th class="p-3 border-b text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr class="hover:bg-gray-50">
                        
                        <td class="p-3 border-b">{{ $producto->id }}</td>
                        
                        <td class="p-3 border-b">
                            @if($producto->url_imagen_principal)
                                @if(str_starts_with($producto->url_imagen_principal, 'http'))
                                    <img src="{{ $producto->url_imagen_principal }}" alt="Foto Seeder" class="w-16 h-16 object-cover rounded shadow border border-gray-200">
                                @else
                                    <img src="{{ asset('storage/' . $producto->url_imagen_principal) }}" alt="Foto Local" class="w-16 h-16 object-cover rounded shadow border border-gray-200">
                                @endif
                            @else
                                <span class="text-gray-400 text-xs italic">Sin foto</span>
                            @endif
                        </td>   
                        
                        <td class="p-3 border-b font-semibold">{{ $producto->nombre }}</td>
                        
                        <td class="p-3 border-b">{{ $producto->precio }} €</td>
                        
                        <td class="p-3 border-b">
                            <span class="{{ $producto->stock < 5 ? 'text-red-500 font-bold' : 'text-gray-700' }}">
                                {{ $producto->stock }}
                            </span>
                        </td>
                        
                        <td class="p-3 border-b text-center space-x-2">
                            <a href="{{ route('admin.productos.edit', $producto->id) }}" class="text-yellow-600 hover:text-yellow-800 font-bold">Editar</a>
                            
                            <form action="{{ route('admin.productos.destroy', $producto->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que quieres borrar este producto?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-bold">Borrar</button>
                            </form>
                        </td>
                    </tr>
                    @if($producto->estado === 'devolucion_solicitada')
                        <form action="{{ route('admin.pedidos.aprobar-devolucion', $producto->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Confirmar que el paquete ha llegado bien? Se devolverá el stock.');">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success btn-sm">
                                Aprobar Devolución
                            </button>
                        </form>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="p-3 border-b text-center text-gray-500">No hay productos en la base de datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $productos->links() }}
        </div>

    </div>
</body>
</html>