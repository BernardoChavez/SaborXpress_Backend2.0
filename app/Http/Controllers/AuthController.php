<?php

namespace App\Http\Controllers;

use App\Models\Autenticacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $fields = $request->validate([
            'correo' => 'required|string|email',
            'contrasena' => 'required|string|min:6'
        ], [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos :min caracteres.',
        ]);

        $userAuth = Autenticacion::where('correo', $fields['correo'])->first();

        if (!$userAuth || !Hash::check($fields['contrasena'], $userAuth->contrasena)) {
            return response()->json(['message' => 'Correo o contraseña incorrectos.'], 401);
        }

        // Creamos el token con la habilidad del rol (Admin, Cajero, etc.)
        $token = $userAuth->createToken('saborxpress_token', [$userAuth->tipo_usuario])->plainTextToken;

        return response()->json([
            'user' => $userAuth,
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
}