<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Paquete1Seguridad\Models\Paquete;
use Modules\Paquete1Seguridad\Models\CasoUso;
use Modules\Paquete1Seguridad\Models\Rol;
use Modules\Paquete1Seguridad\Models\Autenticacion;
use Modules\Paquete1Seguridad\Models\PermisoRol;
use Modules\Paquete2Usuarios\Models\Persona;
use Illuminate\Support\Facades\Hash;

class SaborXpressProyectSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREAR PAQUETES (Estructura del Backend)
        $p1 = Paquete::updateOrCreate(['codigo' => 'P1'], ['nombre' => 'Paquete 1: Seguridad']);
        $p2 = Paquete::updateOrCreate(['codigo' => 'P2'], ['nombre' => 'Paquete 2: Usuarios']);
        $p3 = Paquete::updateOrCreate(['codigo' => 'P3'], ['nombre' => 'Paquete 3: Configuración']);
        $p4 = Paquete::updateOrCreate(['codigo' => 'P4'], ['nombre' => 'Paquete 4: Inventarios']);
        $p5 = Paquete::updateOrCreate(['codigo' => 'P5'], ['nombre' => 'Paquete 5: Ventas']);

        // 2. CREAR CASOS DE USO
        $casos = [
            ['P1', 'CU6', 'Asignar Privilegios (Roles)', true],
            ['P1', 'CU7', 'Consultar Bitácora', false],
            ['P1', 'CU1', 'Inicio de Sesión', false],
            ['P2', 'CU5', 'Gestionar Usuarios', true],
            ['P3', 'CU8', 'Gestionar Catálogo y Productos', true],
            ['P3', 'CU15', 'Gestión de Empresa', true],
            ['P4', 'CU30', 'Control de Stock e Inventario', true],
            ['P4', 'CU31', 'Gestión de Recetas y Fichas', true],
            ['P5', 'CU16', 'Apertura/Cierre de Caja', true],
            ['P5', 'CU17', 'Punto de Venta (POS)', true],
            ['P5', 'CU20', 'Monitor de Cocina', true],
        ];

        foreach ($casos as $c) {
            $paqId = match($c[0]) {
                'P1' => $p1->id, 'P2' => $p2->id, 'P3' => $p3->id, 'P4' => $p4->id, 'P5' => $p5->id,
            };
            CasoUso::updateOrCreate(
                ['codigo' => $c[1]],
                ['nombre' => $c[2], 'id_paquete' => $paqId, 'es_crud' => $c[3]]
            );
        }

        // 3. CREAR ROLES BÁSICOS
        $adminRol = Rol::updateOrCreate(['nombre' => 'Admin']);
        Rol::updateOrCreate(['nombre' => 'Cajero']);
        Rol::updateOrCreate(['nombre' => 'Cocinero']);

        // 4. ASIGNAR TODOS LOS PERMISOS AL ADMIN AUTOMÁTICAMENTE
        $todosLosCasos = CasoUso::all();
        foreach ($todosLosCasos as $cu) {
            PermisoRol::updateOrCreate(
                ['id_rol' => $adminRol->id, 'id_caso_uso' => $cu->id],
                ['puede_ver' => true, 'puede_crear' => true, 'puede_editar' => true, 'puede_eliminar' => true]
            );
        }

        // 5. CREAR USUARIO ADMINISTRADOR INICIAL
        $persona = Persona::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['telefono' => '70000000']
        );

        Autenticacion::updateOrCreate(
            ['correo' => 'admin@saborxpress.com'],
            [
                'id_persona' => $persona->id,
                'contrasena' => Hash::make('admin123'),
                'id_rol' => $adminRol->id,
                'intentos_fallidos' => 0
            ]
        );

        $this->command->info('Base de datos SaborXpress sincronizada y lista para Git.');
    }
}
