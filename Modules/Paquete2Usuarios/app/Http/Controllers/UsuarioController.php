<?php

namespace Modules\Paquete2Usuarios\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Paquete2Usuarios\Models\Persona;
use Modules\Paquete1Seguridad\Models\Autenticacion;
use Modules\Paquete1Seguridad\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function store(Request $request)
    {
        if ($request->input('telefono') === '') {
            $request->merge(['telefono' => null]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s]{7,20}$/',
            'correo' => 'required|email|unique:autenticacion,correo',
            'contrasena' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'tipo_usuario' => 'required|string|exists:roles,nombre'
        ]);

        return DB::transaction(function () use ($validated) {
            $persona = Persona::create([
                'nombre' => $validated['nombre'],
                'telefono' => $validated['telefono']
            ]);

            $rol = Rol::where('nombre', $validated['tipo_usuario'])->first();

            $auth = Autenticacion::create([
                'id_persona' => $persona->id,
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'id_rol' => $rol->id
            ]);

            return response()->json([
                'message' => 'Usuario creado con éxito',
                'persona' => $persona,
                'auth' => $auth
            ], 201);
        });
    }

    public function index()
    {
        $usuarios = Autenticacion::with(['persona', 'rol'])->get()->map(function ($u) {
            $u->tipo_usuario = $u->rol ? $u->rol->nombre : 'Desconocido';
            return $u;
        });
        return $usuarios;
    }

    public function show($id)
    {
        $usuario = Autenticacion::with(['persona', 'rol'])->find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        
        $usuario->tipo_usuario = $usuario->rol ? $usuario->rol->nombre : 'Desconocido';

        return response()->json($usuario, 200);
    }

    public function update(Request $request, $id)
    {
        $usuario = Autenticacion::with('persona')->find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($request->input('telefono') === '') {
            $request->merge(['telefono' => null]);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s]{7,20}$/',
            'correo' => 'sometimes|required|email|unique:autenticacion,correo,' . $usuario->id_persona . ',id_persona',
            'contrasena' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'tipo_usuario' => 'sometimes|required|string|exists:roles,nombre'
        ]);

        return DB::transaction(function () use ($validated, $usuario) {
            if (isset($validated['nombre']) || isset($validated['telefono'])) {
                $usuario->persona->update([
                    'nombre' => $validated['nombre'] ?? $usuario->persona->nombre,
                    'telefono' => array_key_exists('telefono', $validated) ? $validated['telefono'] : $usuario->persona->telefono,
                ]);
            }

            $authData = [];
            if (isset($validated['correo'])) $authData['correo'] = $validated['correo'];
            if (isset($validated['tipo_usuario'])) {
                $rol = Rol::where('nombre', $validated['tipo_usuario'])->first();
                if ($rol) $authData['id_rol'] = $rol->id;
            }
            if (!empty($validated['contrasena'])) {
                $authData['contrasena'] = Hash::make($validated['contrasena']);
            }

            if (!empty($authData)) {
                $usuario->update($authData);
            }

            $usuarioRefresh = $usuario->fresh(['persona', 'rol']);
            $usuarioRefresh->tipo_usuario = $usuarioRefresh->rol ? $usuarioRefresh->rol->nombre : 'Desconocido';

            return response()->json([
                'message' => 'Usuario actualizado con éxito',
                'usuario' => $usuarioRefresh
            ], 200);
        });
    }

    public function destroy($id)
    {
        $usuario = Autenticacion::find($id);
        if (!$usuario) return response()->json(['message' => 'Usuario no encontrado'], 404);

        return DB::transaction(function () use ($usuario) {
            $personaId = $usuario->id_persona;
            $usuario->delete();
            Persona::where('id', $personaId)->delete();
            return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
        });
    }
}

