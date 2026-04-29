<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\AdminOutfitWizardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::get('/', [ProductoController::class, 'index'])->name('home');

// Ruta pública para la IA
Route::post('/outfit-wizard', [OutfitWizardController::class, 'generate']);
// Ruta alternativa si quieres una url tipo /catalogo
Route::get('/catalogo', [ProductoController::class, 'index'])->name('productos.index');

// Ruta para ver el detalle de un producto (Comparador)
Route::get('/producto/{slug}', [ProductoController::class, 'show'])->name('productos.show');

// Rutas públicas (El formulario de login)
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
Route::get('/admin/logout', [AuthController::class, 'logout']);

// Rutas para el Panel de Administrador
Route::prefix('admin')->middleware(['admin'])->group(function () {
    
    // Rutas de Productos
    Route::resource('productos', AdminProductoController::class)->names('admin.productos');
    Route::put('/pedidos/{id}/aprobar-devolucion', [AdminProductoController::class, 'aprobarDevolucion'])
    ->name('admin.pedidos.aprobar-devolucion');

    Route::post('/productos/{id}/eliminar-imagen', [AdminProductoController::class, 'eliminarImagen'])
         ->name('admin.productos.eliminarImagen');

    // Rutas de Usuarios
    Route::prefix('usuarios')->name('admin.usuarios.')->middleware(['verificar.rol:admin_usuarios,admin'])->group(function () {
        Route::get('/', [AdminUsuarioController::class, 'index'])->name('index');
        Route::get('/create', [AdminUsuarioController::class, 'create'])->name('create');
        Route::post('/', [AdminUsuarioController::class, 'store'])->name('store');
        Route::get('/{usuario}/edit', [AdminUsuarioController::class, 'edit'])->name('edit');
        Route::put('/{usuario}', [AdminUsuarioController::class, 'update'])->name('update');
        Route::patch('/{usuario}/toggle-status', [AdminUsuarioController::class, 'toggleStatus'])->name('toggleStatus');
        Route::delete('/{usuario}/force-delete', [AdminUsuarioController::class, 'forceDelete'])->name('forceDelete');
        Route::get('/{usuario}', [AdminUsuarioController::class, 'show'])->name('show');
        Route::post('/{usuario}/recomendar', [AdminUsuarioController::class, 'enviarRecomendacion'])->name('recomendar');
    });
});

// PREPARAR USUARIO TEST PARA RECOMENDACIONES (USAR Y TIRAR)
Route::get('/preparar-usuario-test', function () {
    // 1. Buscamos o creamos tu usuario
    $user = App\Models\User::firstOrCreate(
        ['email' => 'pablo.lomana.h@gmail.com'],
        [
            'name' => 'Pablo (Test Recomendar)',
            'password' => bcrypt('password123'),
            'rol' => 'cliente',
            'is_active' => true,
        ]
    );

    // 2. Buscamos una categoría que tenga al menos 2 productos (para que haya algo que recomendar)
    $categoriaConVarios = App\Models\Producto::select('categoria_id')
        ->where('stock', '>', 0)
        ->groupBy('categoria_id')
        ->havingRaw('COUNT(*) > 1')
        ->inRandomOrder()
        ->first();

    if (!$categoriaConVarios) {
        return "Error: Necesitas tener al menos una categoría con 2 o más productos con stock para poder hacer recomendaciones.";
    }

    $productoParaComprar = App\Models\Producto::where('categoria_id', $categoriaConVarios->categoria_id)
        ->where('stock', '>', 0)
        ->inRandomOrder()
        ->first();

    // 3. Le creamos un pedido completado a ese usuario
    $order = App\Models\Order::create([
        'user_id' => $user->id,
        'total' => $productoParaComprar->precio,
        'estado' => 'pagado',
        'nombre' => 'Pablo',
        'apellidos' => 'Lomana',
        'telefono' => '123456789',
        'direccion' => 'Calle de Prueba 123',
        'ciudad' => 'Madrid',
        'provincia' => 'Madrid',
        'codigo_postal' => '28000',
    ]);

    // 4. Metemos el producto en el pedido
    App\Models\OrderItem::create([
        'order_id' => $order->id,
        'producto_id' => $productoParaComprar->id,
        'cantidad' => 1,
        'precio_unitario' => $productoParaComprar->precio,
    ]);

    return "<h2>¡Todo listo! 🚀</h2>
            <p>Se ha asegurado la existencia del usuario <b>{$user->email}</b>.</p>
            <p>Se le ha creado una compra ficticia del producto: <b>{$productoParaComprar->nombre}</b>.</p>
            <p><b>Siguiente paso:</b> Ve a tu panel de administrador (Usuarios), busca este correo y dale al botón de Enviar Recomendación.</p>";
});