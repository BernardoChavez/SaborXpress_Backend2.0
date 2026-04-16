<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;

// --- RUTA PÚBLICA ---
Route::post('/login', [AuthController::class, 'login']);

// --- RUTAS PROTEGIDAS (Requieren Token y Bitácora) ---
Route::middleware(['auth:sanctum', 'bitacora'])->group(function () {
    
    // Solo Administradores
    Route::middleware('ability:Admin')->group(function () {
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::get('/bitacora', function() {
            return \App\Models\Bitacora::all();
        });
    });

    // Accesible para todos los logueados
    Route::get('/productos', [ProductoController::class, 'index']);
});