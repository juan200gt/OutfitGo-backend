<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas de Productos
Route::get('/', [ProductoController::class, 'index'])->name('home');
Route::get('/catalogo', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/producto/{slug}', [ProductoController::class, 'show'])->name('productos.show');

// Redireccionar login antiguo de admin al login de Breeze
Route::redirect('/admin/login', '/login');

// Ruta de compatibilidad para dashboard requerida por tests de Breeze
Route::get('/dashboard', function () {
    return redirect()->route('home');
})->name('dashboard');

// Ruta de compatibilidad para cerrar sesión del admin antiguo (GET/POST)
Route::match(['get', 'post'], '/admin/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
     ->name('admin.logout');

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

// Rutas de Perfil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de Autenticación de Breeze
require __DIR__.'/auth.php';

// Documentación de la API (Swagger UI)
Route::get('/api/documentation', function () {
    return view('swagger');
})->name('api.documentation');


