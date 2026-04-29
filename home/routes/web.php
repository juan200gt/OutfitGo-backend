<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\AdminOutfitWizardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::get('/', [ProductoController::class, 'index'])->name('home');


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

