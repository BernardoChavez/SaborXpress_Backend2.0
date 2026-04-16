<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\BitacoraController;

/*
|--------------------------------------------------------------------------
| API Routes - SaborXpress
|--------------------------------------------------------------------------
*/

// --- RUTA PÚBLICA (CU 1) ---
Route::post('/login', [AuthController::class, 'login']);

// --- RUTAS PROTEGIDAS Y AUDITADAS (Middleware: Sanctum + Bitácora) ---
Route::middleware(['auth:sanctum', 'bitacora'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // 🔐 SOLO ADMINISTRADORES (CU 3, 5, 6, 9)
    Route::middleware('abilities:Admin')->group(function () {
        
        // Gestión de Usuarios (CU 3, CU 5)
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::get('/usuarios', [UsuarioController::class, 'index']);
        Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->whereNumber('id');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->whereNumber('id');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->whereNumber('id');
        
        // Gestión de Catálogo - Escritura (CU 9)
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::put('/productos/{id}', [ProductoController::class, 'update'])->whereNumber('id');
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->whereNumber('id');
        
        Route::post('/categorias', [CategoriaController::class, 'store']);
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->whereNumber('id');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->whereNumber('id');
        
        // Auditoría (CU 6)
        Route::get('/bitacora', [BitacoraController::class, 'index']);
    });

    // 🔓 ACCESO GENERAL (Admin, Cajero, Cocinero)
    // Gestión de Catálogo - Lectura (CU 9)
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->whereNumber('id');
    
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::get('/categorias/{id}', [CategoriaController::class, 'show'])->whereNumber('id');
});