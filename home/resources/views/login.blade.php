<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    @if(isset($show_admin_buttons) && $show_admin_buttons)
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Admin Global</h2>
            <p class="text-center text-gray-600 mb-6">¿A qué panel deseas acceder?</p>
            
            <div class="flex flex-col space-y-4">
                <a href="{{ route('admin.usuarios.index') }}" class="block w-full text-center bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700 transition shadow-lg">
                    Ir a Usuarios
                </a>
                <a href="{{ route('admin.productos.index') }}" class="block w-full text-center bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition shadow-lg">
                    Ir a Productos
                </a>
            </div>

            <form action="{{ route('admin.logout') }}" method="POST" class="mt-6">
                @csrf
                <button type="submit" class="w-full text-center bg-gray-200 text-gray-700 font-semibold py-2 rounded hover:bg-gray-300 transition">Cerrar Sesión</button>
            </form>
        </div>
    @else
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Acceso Admin</h1>

            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" class="w-full border p-2 rounded" placeholder="administrador@gmail.com" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                    <input type="password" name="password" class="w-full border p-2 rounded" placeholder="********" required>
                </div>
                <button type="submit" class="w-full bg-gray-800 text-white font-bold py-2 rounded hover:bg-gray-900 transition">
                    Entrar al Panel
                </button>
                <a href="https://outfitgo.duckdns.org/" class="block w-full text-center bg-gray-100 text-gray-700 font-semibold py-2 rounded border border-gray-300 hover:bg-gray-200 transition mt-2">
                    &larr; Volver a la Tienda Principal
                </a>
            </form>
        </div>
    @endif

</body>
</html>