<?php

// ── MIDDLEWARE: BitacoraMiddleware ──────────────────────────────────────────
// PROPÓSITO: Auditoría Forense Técnica. Registra quién, cuándo y qué hizo.
// ─────────────────────────────────────────────────────────────────────────────

namespace App\Http\Middleware;

use Closure;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BitacoraMiddleware
{
    /**
     * MOTOR DE AUDITORÍA: Captura acciones CRUD (Create, Update, Delete)
     * automáticamente basándose en el método HTTP.
     */
    public function handle($request, Closure $next)
    {
        $startTime = Carbon::now();
        $response = $next($request);

        if (Auth::check()) {
            try {
                $userId = Auth::id();
                $method = strtoupper($request->method());
                $path = $request->path();

                // --- FILTRO DE EXCLUSIÓN ---
                // No grabamos LOGIN ni LOGOUT aquí porque ya se graban manualmente en el AuthController
                $excludedPaths = ['api/logout', 'api/login'];
                if (in_array($path, $excludedPaths)) {
                    return $response;
                }

                // Registrar solo cambios o auditoría específica
                if (in_array($method, ['POST', 'PUT', 'DELETE']) || $path === 'api/bitacora') {
                    
                    $endTime = Carbon::now();

                    Bitacora::create([
                        'id_usuario' => $userId,
                        'accion' => "{$method} /{$path}",
                        'accion_detalle' => $this->buildActionDetail($request),
                        'ip' => $request->ip(),
                        'fecha' => $startTime->toDateString(),
                        'hora_inicio' => $startTime->format('H:i:s'),
                        'hora_cierre' => $endTime->format('H:i:s'),
                    ]);
                }
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

        // Determinar prefijo CRUD
        $prefix = '';
        if ($method === 'POST')   $prefix = 'AÑADIÓ: ';
        if ($method === 'PUT')    $prefix = 'MODIFICÓ: ';
        if ($method === 'DELETE') $prefix = 'ELIMINÓ: ';

        // Descripciones Amigables
        if ($path === '/api/usuarios' && $method === 'POST') return "AÑADIÓ al usuario: " . $request->input('persona.nombre', 'Nuevo');
        if (preg_match('#^/api/usuarios/\d+$#', $path) && $method === 'PUT') return "MODIFICÓ los datos de un usuario";
        
        if ($path === '/api/productos' && $method === 'POST') return "AÑADIÓ el producto: " . $request->input('nombre', 'Nuevo');
        if (preg_match('#^/api/productos/\d+$#', $path) && $method === 'PUT') return "MODIFICÓ un producto del catálogo";
        if (preg_match('#^/api/productos/\d+$#', $path) && $method === 'DELETE') return "ELIMINÓ un producto del catálogo";

        if ($path === '/api/roles' && $method === 'PUT') return "MODIFICÓ la matriz de permisos de seguridad";
        if ($path === '/api/ventas' && $method === 'POST') return "REGISTRÓ una venta por Bs. " . $request->input('monto_total', '0');

        if ($path === '/api/bitacora') return "CONSULTÓ el historial de auditoría técnica";

        // Por defecto si no coincide con los anteriores
        return "Realizó una operación en el módulo {$path}";
    }
}