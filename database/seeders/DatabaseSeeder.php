<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Paquete3Configuracion\Models\Categoria;
use Modules\Paquete3Configuracion\Models\Producto;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ejecutar el Seeder Maestro (Paquetes, Roles, Usuarios, Permisos)
        $this->call(SaborXpressProyectSeeder::class);

        // 2. Sembrar datos de prueba para el Catálogo (Opcional pero recomendado)
        $cat1 = Categoria::updateOrCreate(['nombre' => 'Platos Principales']);
        $cat2 = Categoria::updateOrCreate(['nombre' => 'Bebidas']);

        Producto::updateOrCreate(
            ['nombre' => 'Salchipapa Especial'],
            [
                'descripcion' => 'Papas fritas con salchicha premium y salsas',
                'precio_venta' => 18.00,
                'id_categoria' => $cat1->id
            ]
        );

        Producto::updateOrCreate(
            ['nombre' => 'Coca Cola 500ml'],
            [
                'descripcion' => 'Refresco personal',
                'precio_venta' => 6.00,
                'id_categoria' => $cat2->id
            ]
        );
    }
}