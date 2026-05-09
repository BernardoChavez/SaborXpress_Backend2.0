<?php

use Illuminate\Support\Facades\Route;
use Modules\Paquete1Seguridad\Http\Controllers\AuthController;
use Modules\Paquete1Seguridad\Http\Controllers\PasswordRecoveryController;
use Modules\Paquete1Seguridad\Http\Controllers\RolesController;
use Modules\Paquete3Configuracion\Http\Controllers\ProductoController;
use Modules\Paquete3Configuracion\Http\Controllers\CategoriaController;
use Modules\Paquete3Configuracion\Http\Controllers\EmpresaController;
use Modules\Paquete2Usuarios\Http\Controllers\UsuarioController;
use Modules\Paquete5Ventas\Http\Controllers\CajaController;
use Modules\Paquete5Ventas\Http\Controllers\VentaController;
use Modules\Paquete5Ventas\Http\Controllers\CocinaController;
use Modules\Paquete4Inventarios\Http\Controllers\InventarioController;
use App\Http\Controllers\BitacoraController;

/*
|--------------------------------------------------------------------------
| API Routes - SaborXpress
|--------------------------------------------------------------------------
*/

// --- RUTA PÚBLICA (CU 1) ---
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/login', function() {
    return response()->json(['message' => 'Sesión expirada.'], 401);
});
Route::post('/register', [AuthController::class, 'register']);

// --- RECUPERACIÓN DE CONTRASEÑA (CU 3) - PÚBLICAS ---
Route::post('/password/forgot', [PasswordRecoveryController::class, 'sendCode']);
Route::post('/password/verify', [PasswordRecoveryController::class, 'verifyCode']);
Route::post('/password/reset',  [PasswordRecoveryController::class, 'resetPassword']);

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
        
                        // Gestión de Roles y Permisos (CU 6)
        Route::get('/roles/estructura', [RolesController::class, 'getEstructura']);
        Route::get('/roles', [RolesController::class, 'index']);
        Route::post('/roles', [RolesController::class, 'store']);
        Route::get('/roles/{id}', [RolesController::class, 'show'])->whereNumber('id');
        Route::put('/roles/{id}', [RolesController::class, 'update'])->whereNumber('id');
        Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->whereNumber('id');

        // Gestión de Empresa (CU 15)
        Route::get('/empresa', [EmpresaController::class, 'show']);
        Route::put('/empresa', [EmpresaController::class, 'update']);
        
        // Auditoría (CU 6)
        Route::get('/bitacora', [BitacoraController::class, 'index']);
    });

    // 🔓 ACCESO GENERAL (Admin, Cajero, Cocinero)
    // Gestión de Catálogo - Lectura (CU 9)
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->whereNumber('id');
    
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::get('/categorias/{id}', [CategoriaController::class, 'show'])->whereNumber('id');

    // 💰 GESTIÓN DE CAJA (CU 16)
    Route::get('/caja/estado', [CajaController::class, 'getEstadoActual']);
    Route::post('/caja/abrir', [CajaController::class, 'abrir']);
    Route::post('/caja/cerrar', [CajaController::class, 'cerrar']);

    // 📦 GESTIÓN DE INVENTARIO (CU 12, 13, 38)
    Route::get('/inventario/bruto', [InventarioController::class, 'indexBruto']);
    Route::post('/inventario/bruto', [InventarioController::class, 'storeBruto']);
    Route::put('/inventario/bruto/{id}', [InventarioController::class, 'updateBruto'])->whereNumber('id');
    
    Route::get('/inventario/procesado', [InventarioController::class, 'indexProcesado']);
    Route::post('/inventario/procesado', [InventarioController::class, 'storeProcesado']);
    Route::put('/inventario/procesado/{id}', [InventarioController::class, 'updateProcesado'])->whereNumber('id');
    
    Route::post('/inventario/transformar', [InventarioController::class, 'transformar']);
    
    Route::get('/inventario/fichas', [InventarioController::class, 'indexFichas']);
    Route::post('/inventario/fichas', [InventarioController::class, 'storeFicha']);

    Route::get('/inventario/recetas', [InventarioController::class, 'indexRecetas']);
    Route::post('/inventario/recetas', [InventarioController::class, 'storeReceta']);
    Route::get('/inventario/recetas/{id_producto}', [InventarioController::class, 'getRecetas'])->whereNumber('id_producto');

    // 🛒 GESTIÓN DE VENTAS (POS - CU 17, 19, 32)
    Route::get('/ventas', [VentaController::class, 'index']);
    Route::post('/ventas', [VentaController::class, 'store']);

    // 👨‍🍳 GESTIÓN DE COCINA (CU 20, 22)
    Route::get('/cocina/comandas', [CocinaController::class, 'index']);
    Route::put('/cocina/comandas/{id}', [CocinaController::class, 'updateEstado'])->whereNumber('id');
});


