<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Autenticacion;
use Illuminate\Http\Request;
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
            'contrasena' => 'required|string|min:8',
            'tipo_usuario' => 'required|in:Admin,Cajero,Cocinero'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar :max caracteres.',
            'telefono.regex' => 'El teléfono debe tener entre 7 y 20 dígitos (puedes usar +, espacios o guiones).',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.unique' => 'Ese correo ya está registrado. Usa otro.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos :min caracteres.',
            'tipo_usuario.required' => 'Selecciona un rol.',
            'tipo_usuario.in' => 'El rol seleccionado no es válido.',
        ]);

        // Usamos una transacción por si algo falla en la segunda tabla
        return DB::transaction(function () use ($validated) {
            $persona = Persona::create([
                'nombre' => $validated['nombre'],
                'telefono' => $validated['telefono']
            ]);

            $auth = Autenticacion::create([
                'id_persona' => $persona->id,
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'tipo_usuario' => $validated['tipo_usuario']
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
        // Retorna todos los usuarios con su información de persona
        return Autenticacion::with('persona')->get();
    }

    public function show($id)
    {
        $usuario = Autenticacion::with('persona')->find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

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
            'contrasena' => 'nullable|string|min:8',
            'tipo_usuario' => 'sometimes|required|in:Admin,Cajero,Cocinero'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar :max caracteres.',
            'telefono.regex' => 'El teléfono debe tener entre 7 y 20 dígitos (puedes usar +, espacios o guiones).',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'correo.unique' => 'Ese correo ya está registrado. Usa otro.',
            'contrasena.min' => 'La contraseña debe tener al menos :min caracteres.',
            'tipo_usuario.required' => 'Selecciona un rol.',
            'tipo_usuario.in' => 'El rol seleccionado no es válido.',
        ]);

        return DB::transaction(function () use ($validated, $usuario) {
            // Actualizar tabla persona
            if (isset($validated['nombre']) || isset($validated['telefono'])) {
                $usuario->persona->update([
                    'nombre' => $validated['nombre'] ?? $usuario->persona->nombre,
                    'telefono' => array_key_exists('telefono', $validated) ? $validated['telefono'] : $usuario->persona->telefono,
                ]);
            }

            // Actualizar tabla autenticacion
            $authData = [];
            if (isset($validated['correo'])) $authData['correo'] = $validated['correo'];
            if (isset($validated['tipo_usuario'])) $authData['tipo_usuario'] = $validated['tipo_usuario'];
            if (!empty($validated['contrasena'])) {
                $authData['contrasena'] = Hash::make($validated['contrasena']);
            }

            if (!empty($authData)) {
                $usuario->update($authData);
            }

            return response()->json([
                'message' => 'Usuario actualizado con éxito',
                'usuario' => $usuario->fresh('persona')
            ], 200);
        });
    }

    public function destroy($id)
    {
        $usuario = Autenticacion::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return DB::transaction(function () use ($usuario) {
            $personaId = $usuario->id_persona;
            $usuario->delete();
            Persona::where('id', $personaId)->delete();

            return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
        });
    }
}