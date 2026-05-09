<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Paquete2Usuarios\Models\Persona;
use Modules\Paquete1Seguridad\Models\Autenticacion;
use Modules\Paquete3Configuracion\Models\Categoria;
use Modules\Paquete3Configuracion\Models\Producto;
use Modules\Paquete1Seguridad\Models\Rol;
use Modules\Paquete1Seguridad\Models\Paquete;
use Modules\Paquete1Seguridad\Models\CasoUso;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Crear Roles y Permisos Iniciales (CU6)
        $rolAdmin = Rol::create(['nombre' => 'Admin']);
        $rolCajero = Rol::create(['nombre' => 'Cajero']);
        $rolCocinero = Rol::create(['nombre' => 'Cocinero']);
        $rolCliente = Rol::create(['nombre' => 'Cliente']);

        $paqSeguridad = Paquete::create(['nombre' => 'Seguridad y Administración', 'codigo' => 'PAQ1']);
        $cuRoles = CasoUso::create(['id_paquete' => $paqSeguridad->id, 'codigo' => 'CU6', 'nombre' => 'Gestión de Roles y Niveles', 'es_crud' => true]);
        $cuBitacora = CasoUso::create(['id_paquete' => $paqSeguridad->id, 'codigo' => 'CU_BIT', 'nombre' => 'Bitácora de Sistema', 'es_crud' => false]);
        $cuEmpresa = CasoUso::create(['id_paquete' => $paqSeguridad->id, 'codigo' => 'CU15', 'nombre' => 'Configuración de Empresa', 'es_crud' => false]);
        
        $paqUsuarios = Paquete::create(['nombre' => 'Gestión de Usuarios', 'codigo' => 'PAQ2']);
        $cuUsuarios = CasoUso::create(['id_paquete' => $paqUsuarios->id, 'codigo' => 'CU5', 'nombre' => 'Gestionar Usuarios (Personal)', 'es_crud' => true]);

        $paqInventario = Paquete::create(['nombre' => 'Inventario y Catálogo', 'codigo' => 'PAQ3']);
        $cuCatalogo = CasoUso::create(['id_paquete' => $paqInventario->id, 'codigo' => 'CU10', 'nombre' => 'Gestionar Categorías y Productos', 'es_crud' => true]);

        // Asignar permisos al Admin para todo
        $casosUso = CasoUso::all();
        foreach ($casosUso as $cu) {
            $rolAdmin->permisos()->create([
                'id_caso_uso' => $cu->id,
                'puede_ver' => true,
                'puede_crear' => true,
                'puede_editar' => true,
                'puede_eliminar' => true,
            ]);
        }

        // 1. Usuarios (CU 1, 3, 5)
        $adminP = Persona::create(['nombre' => 'Bernardo Admin', 'telefono' => '70011223']);
        Autenticacion::create([
            'id_persona' => $adminP->id,
            'correo' => 'admin@saborxpress.com',
            'contrasena' => Hash::make('admin123'),
            'id_rol' => $rolAdmin->id
        ]);

        $cajeroP = Persona::create(['nombre' => 'Juan Cajero', 'telefono' => '60055443']);
        Autenticacion::create([
            'id_persona' => $cajeroP->id,
            'correo' => 'cajero@saborxpress.com',
            'contrasena' => Hash::make('cajero123'),
            'id_rol' => $rolCajero->id
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