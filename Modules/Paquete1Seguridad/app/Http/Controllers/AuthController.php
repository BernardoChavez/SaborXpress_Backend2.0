<?php

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

        // Si no existe el usuario, retorno error genérico
        if (!$userAuth) {
            return response()->json(['message' => 'Correo o contraseña incorrectos.'], 401);
        }

        // Verificar bloqueo (CU4)
        if ($userAuth->bloqueado_hasta && Carbon::now()->lessThan($userAuth->bloqueado_hasta)) {
            $segundos = Carbon::now()->diffInSeconds($userAuth->bloqueado_hasta);
            return response()->json([
                'message' => 'Cuenta bloqueada temporalmente por demasiados intentos fallidos. Intenta en ' . $segundos . ' segundos.'
            ], 403);
        }

        // Si la contraseña es incorrecta
        if (!Hash::check($fields['contrasena'], $userAuth->contrasena)) {
            $userAuth->intentos_fallidos += 1;
            
            // Si llega a 3 intentos fallidos, bloquear por 1 minuto
            if ($userAuth->intentos_fallidos >= 3) {
                $userAuth->bloqueado_hasta = Carbon::now()->addMinutes(1);
                $userAuth->save();
                return response()->json(['message' => 'Cuenta bloqueada por 1 minuto debido a 3 intentos fallidos.'], 403);
            }
            
            $userAuth->save();
            return response()->json([
                'message' => 'Contraseña incorrecta. Te quedan ' . (3 - $userAuth->intentos_fallidos) . ' intentos.'
            ], 401);
        }

        // Si el login es exitoso, resetear intentos
        $userAuth->intentos_fallidos = 0;
        $userAuth->bloqueado_hasta = null;
        $userAuth->save();

        // Obtener el rol real de la tabla roles
        $rol = Rol::find($userAuth->id_rol);
        $nombreRol = $rol ? $rol->nombre : 'SinRol';

        // Creamos el token con la habilidad del rol
        $token = $userAuth->createToken('saborxpress_token', [$nombreRol])->plainTextToken;

        return response()->json([
            'user' => [
                'id_persona' => $userAuth->id_persona,
                'correo' => $userAuth->correo,
                'tipo_usuario' => $nombreRol // Mantenemos tipo_usuario en la respuesta para no romper el front
            ],
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Sesión cerrada con éxito'
        ], 200);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|unique:autenticacion,correo',
            'contrasena' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
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

            $token = $userAuth->createToken('saborxpress_token', ['Cliente'])->plainTextToken;

            return response()->json([
                'user' => [
                    'id_persona' => $userAuth->id_persona,
                    'correo' => $userAuth->correo,
                    'tipo_usuario' => 'Cliente'
                ],
                'token' => $token
            ], 201);
        });
    }
}

