<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use App\Models\Autenticacion;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios (CU 1, 3, 5)
        $adminP = Persona::create(['nombre' => 'Bernardo Admin', 'telefono' => '70011223']);
        Autenticacion::create([
            'id_persona' => $adminP->id,
            'correo' => 'admin@saborxpress.com',
            'contrasena' => Hash::make('admin123'),
            'tipo_usuario' => 'Admin'
        ]);

        $cajeroP = Persona::create(['nombre' => 'Juan Cajero', 'telefono' => '60055443']);
        Autenticacion::create([
            'id_persona' => $cajeroP->id,
            'correo' => 'cajero@saborxpress.com',
            'contrasena' => Hash::make('cajero123'),
            'tipo_usuario' => 'Cajero'
        ]);

        // 2. Catálogo (CU 9)
        $cat1 = Categoria::create(['nombre' => 'Pollos a la Brasa']);
        $cat2 = Categoria::create(['nombre' => 'Hamburguesas']);

        Producto::create([
            'nombre' => 'Cuarto de Pollo',
            'descripcion' => 'Pierna con papas y ensalada',
            'precio_venta' => 25.00,
            'id_categoria' => $cat1->id
        ]);

        Producto::create([
            'nombre' => 'Hamburguesa Simple',
            'descripcion' => 'Carne, queso y salsas',
            'precio_venta' => 15.50,
            'id_categoria' => $cat2->id
        ]);
    }
}