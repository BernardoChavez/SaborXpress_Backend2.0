<?php

/**
 * APARTADO: SEGURIDAD Y ACCESO (BACKEND)
 * CONTROLADOR: AuthController.php
 * FUNCIÓN: Procesa el inicio de sesión, valida credenciales, genera el Token JWT
 *          y registra el evento en la Bitácora de Auditoría.
 */

namespace Modules\Paquete1Seguridad\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Paquete1Seguridad\Models\Autenticacion;
use Modules\Paquete1Seguridad\Models\Rol;
use Modules\Paquete2Usuarios\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $fields = $request->validate([
            'correo' => 'required|string|email',
            'contrasena' => 'required|string|min:6'
        ]);

        $userAuth = Autenticacion::where('correo', $fields['correo'])->first();

        if (!$userAuth) {
            return response()->json(['message' => 'Correo o contraseña incorrectos.'], 401);
        }

        if ($userAuth->bloqueado_hasta && Carbon::now()->lessThan($userAuth->bloqueado_hasta)) {
            $segundos = Carbon::now()->diffInSeconds($userAuth->bloqueado_hasta);
            return response()->json([
                'message' => 'Cuenta bloqueada temporalmente. Intenta en ' . $segundos . ' segundos.'
            ], 403);
        }

        if (!Hash::check($fields['contrasena'], $userAuth->contrasena)) {
            $userAuth->intentos_fallidos += 1;
            if ($userAuth->intentos_fallidos >= 3) {
                $userAuth->bloqueado_hasta = Carbon::now()->addMinutes(1);
                $userAuth->save();
                return response()->json(['message' => 'Cuenta bloqueada por 1 minuto.'], 403);
            }
            $userAuth->save();
            return response()->json(['message' => 'Contraseña incorrecta.'], 401);
        }

        $userAuth->intentos_fallidos = 0;
        $userAuth->bloqueado_hasta = null;
        $userAuth->save();

        // Obtener el rol real de la tabla roles
        $rol = Rol::with('permisos.casoUso')->find($userAuth->id_rol);
        $nombreRol = $rol ? $rol->nombre : 'SinRol';
        
        $abilities = [$nombreRol]; // <--- AÑADIMOS EL NOMBRE DEL ROL PARA LAS RUTAS ACTUALES
        
        if ($rol) {
            foreach ($rol->permisos as $permiso) {
                if ($permiso->puede_ver) $abilities[] = $permiso->casoUso->codigo . ":ver";
                if ($permiso->puede_crear) $abilities[] = $permiso->casoUso->codigo . ":crear";
                if ($permiso->puede_editar) $abilities[] = $permiso->casoUso->codigo . ":editar";
                if ($permiso->puede_eliminar) $abilities[] = $permiso->casoUso->codigo . ":eliminar";
            }
        }

        // Creamos el token con las habilidades de la matriz
        $token = $userAuth->createToken('saborxpress_token', $abilities)->plainTextToken;

        // 📝 REGISTRO EN BITÁCORA (Login Exitoso)
        \App\Models\Bitacora::create([
            'id_usuario' => $userAuth->id_persona,
            'accion' => 'LOGIN',
            'accion_detalle' => "El usuario inició sesión como {$nombreRol}",
            'ip' => $request->ip(),
            'fecha' => now()->toDateString(),
            'hora_inicio' => now()->format('H:i:s'),
            'hora_cierre' => now()->format('H:i:s'),
        ]);

        return response()->json([
            'user' => [
                'id_persona' => $userAuth->id_persona,
                'correo' => $userAuth->correo,
                'tipo_usuario' => $rol->nombre,
                'permisos' => $abilities // Enviamos los códigos al front
            ],
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        // 📝 REGISTRO EN BITÁCORA (Logout)
        if ($request->user()) {
            \App\Models\Bitacora::create([
                'id_usuario' => $request->user()->id_persona,
                'accion' => 'LOGOUT',
                'accion_detalle' => 'El usuario cerró su sesión de forma segura',
                'ip' => $request->ip(),
                'fecha' => now()->toDateString(),
                'hora_inicio' => now()->format('H:i:s'),
                'hora_cierre' => now()->format('H:i:s'),
            ]);
        }

        $request->user()->currentAccessToken()->delete();
        return response(['message' => 'Sesión cerrada con éxito'], 200);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|unique:autenticacion,correo',
            'contrasena' => ['required', 'string', Password::min(8)],
        ]);

        return DB::transaction(function () use ($validated) {
            $persona = Persona::create([
                'nombre' => $validated['nombre'],
                'telefono' => $validated['telefono'] ?? null
            ]);

            $rolCliente = Rol::where('nombre', 'Cliente')->first();

            $userAuth = Autenticacion::create([
                'id_persona' => $persona->id,
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'id_rol' => $rolCliente->id,
            ]);

            // Clientes por defecto solo ven catálogo
            $token = $userAuth->createToken('saborxpress_token', ['CU8:ver'])->plainTextToken;

            return response()->json([
                'user' => [
                    'id_persona' => $userAuth->id_persona,
                    'correo' => $userAuth->correo,
                    'tipo_usuario' => 'Cliente',
                    'permisos' => ['CU8:ver']
                ],
                'token' => $token
            ], 201);
        });
    }
}
