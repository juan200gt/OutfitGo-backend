<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha del Cliente - {{ $usuario->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Ficha del Cliente</h1>        
            <a href="{{ route('admin.usuarios.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-bold shadow">
                Volver al listado
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8 border-t-4 border-blue-500">
            <h2 class="text-xl font-bold mb-4">Datos Personales</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-gray-700">
                <p><strong>Nombre:</strong> {{ $usuario->name }}</p>
                <p><strong>Email:</strong> {{ $usuario->email }}</p>
                <p><strong>Teléfono:</strong> {{ $usuario->telefono ?? 'No registrado' }}</p>
                <p><strong>Dirección:</strong> {{ $usuario->direccion ?? 'No registrada' }}</p>
                <p><strong>Ciudad:</strong> {{ $usuario->ciudad ?? '-' }}</p>
                <p><strong>Provincia:</strong> {{ $usuario->provincia ?? '-' }} ({{ $usuario->codigo_postal ?? '-' }})</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Historial de Pedidos ({{ $usuario->orders->count() }})</h2>
            
            @if($usuario->orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700">
                                <th class="p-3 border-b">Nº Pedido</th>
                                <th class="p-3 border-b">Fecha</th>
                                <th class="p-3 border-b">Estado</th>
                                <th class="p-3 border-b">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuario->orders as $pedido)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 border-b font-bold">#{{ $pedido->id }}</td>
                                    <td class="p-3 border-b">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                                    
                                    <td class="p-3 border-b uppercase text-xs font-bold text-gray-600">
                                        {{ $pedido->estado ?? 'Completado' }}
                                    </td>
                                    
                                    <td class="p-3 border-b text-green-600 font-bold">
                                        {{ number_format($pedido->total ?? 0, 2, ',', '.') }} €
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-blue-50 text-blue-600 p-4 rounded text-center">
                    Este usuario aún no ha realizado ningún pedido en la tienda.
                </div>
            @endif
        </div>

        <div class="mt-8 text-right">
            <form method="POST" action="{{ route('admin.usuarios.recomendar', $usuario->id) }}">
                @csrf
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg transition">
                    📧 Enviar Producto Recomendado
                </button>
            </form>
        </div>

</body>
</html>