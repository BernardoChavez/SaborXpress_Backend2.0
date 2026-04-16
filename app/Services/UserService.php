<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\Autenticacion;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function registrarEmpleado(array $data)
    {
        return DB::transaction(function () use ($data) {
            $persona = Persona::create([
                'nombre' => $data['nombre'],
                'telefono' => $data['telefono'] ?? null,
            ]);

            Autenticacion::create([
                'id_persona' => $persona->id,
                'correo' => $data['correo'],
                'contrasena' => Hash::make($data['contrasena']),
                'tipo_usuario' => $data['rol'], 
            ]);

            return Empleado::create([
                'id_persona' => $persona->id,
                'rol' => $data['rol'],
                'activo' => true
            ]);
        });
    }
}