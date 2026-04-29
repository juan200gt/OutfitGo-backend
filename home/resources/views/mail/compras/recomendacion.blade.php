<!DOCTYPE html>
<html>
<head>
    <style>
        .card { border: 1px solid #eee; padding: 20px; text-align: center; font-family: sans-serif; }
        .btn { background: #000; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        img { max-width: 200px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>¡Hola, {{ $user->name }}! 👋</h1>
        <p>Vimos que hace poco compraste en nuestra tienda. Pensamos que este artículo te quedaría genial:</p>
        
        <img src="{{ str_starts_with($producto->url_imagen_principal, 'http') ? $producto->url_imagen_principal : asset('storage/' . $producto->url_imagen_principal) }}" alt="Producto">
        
        <h2>{{ $producto->nombre }}</h2>
        <p style="font-size: 20px; font-weight: bold;">{{ $producto->precio }} €</p>
        
        <br><br>
        <a href="http://outfitgo.duckdns.org/producto/{{ $producto->slug }}" class="btn">Ver producto en la web</a>
        <br><br>
        <p>¡Esperamos verte pronto de nuevo!</p>
    </div>
</body>
</html>