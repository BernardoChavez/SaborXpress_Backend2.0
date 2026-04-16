<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BitacoraMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {
            try {
                $now = Carbon::now();
                $userId = Auth::id();

                Bitacora::where('id_usuario', $userId)
                    ->whereNull('hora_cierre')
                    ->latest('id')
                    ->first()
                    ?->update(['hora_cierre' => $now->format('H:i:s')]);

                Bitacora::create([
                    'id_usuario' => $userId,
                    'accion' => $request->method(),
                    'accion_detalle' => $this->buildActionDetail($request),
                    'ip' => $request->ip(),
                    'fecha' => $now->toDateString(),
                    'hora_inicio' => $now->format('H:i:s'),
                    'hora_cierre' => null,
                ]);
            } catch (\Exception $e) {
                Log::error("Fallo de Bitácora: " . $e->getMessage());
            }
        }

        return $response;
    }

    private function buildActionDetail($request): string
    {
        $method = strtoupper($request->method());
        $path = '/' . ltrim($request->path(), '/');

        if ($method === 'GET' && $path === '/api/bitacora') {
            return 'Consulto la bitacora del sistema';
        }

        if ($method === 'GET' && $path === '/api/productos') {
            return 'Consulto el catalogo de productos';
        }

        if ($method === 'GET' && preg_match('#^/api/productos/\d+$#', $path)) {
            return 'Consulto un producto del menu';
        }

        if ($method === 'POST' && $path === '/api/productos') {
            $nombreProducto = $request->input('nombre', 'sin nombre');

            return "Registro este producto: {$nombreProducto}";
        }

        if ($method === 'PUT' && preg_match('#^/api/productos/\d+$#', $path)) {
            $nombreProducto = $request->input('nombre');

            return $nombreProducto
                ? "Edito este producto: {$nombreProducto}"
                : 'Edito este producto';
        }

        if ($method === 'POST' && $path === '/api/usuarios') {
            $nombre = $request->input('nombre', 'sin nombre');

            return "Registro este usuario: {$nombre}";
        }

        if ($method === 'DELETE' && preg_match('#^/api/productos/\d+$#', $path)) {
            return 'Elimino este producto';
        }

        if ($method === 'GET' && preg_match('#^/api/categorias/\d+$#', $path)) {
            return 'Consulto una categoria';
        }

        if ($method === 'GET' && $path === '/api/usuarios') {
            return 'Consulto la lista de usuarios';
        }

        if ($method === 'GET' && preg_match('#^/api/usuarios/\d+$#', $path)) {
            return 'Consulto un usuario';
        }

        if ($method === 'PUT' && preg_match('#^/api/usuarios/\d+$#', $path)) {
            $nombre = $request->input('nombre');

            return $nombre
                ? "Edito este usuario: {$nombre}"
                : 'Edito este usuario';
        }

        if ($method === 'DELETE' && preg_match('#^/api/usuarios/\d+$#', $path)) {
            return 'Elimino este usuario';
        }

        if ($method === 'GET' && $path === '/api/categorias') {
            return 'Consulto las categorias';
        }

        if ($method === 'POST' && $path === '/api/categorias') {
            $nombreCategoria = $request->input('nombre', 'sin nombre');

            return "Registro esta categoria: {$nombreCategoria}";
        }

        if ($method === 'PUT' && preg_match('#^/api/categorias/\d+$#', $path)) {
            $nombreCategoria = $request->input('nombre');

            return $nombreCategoria
                ? "Edito esta categoria: {$nombreCategoria}"
                : 'Edito esta categoria';
        }

        if ($method === 'DELETE' && preg_match('#^/api/categorias/\d+$#', $path)) {
            return 'Elimino esta categoria';
        }

        if ($method === 'POST' && $path === '/api/logout') {
            return 'Cerro sesion';
        }

        return 'Sin detalle de accion';
    }
}