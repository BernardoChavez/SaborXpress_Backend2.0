<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Paquete1Seguridad\Models\Paquete;
use Modules\Paquete1Seguridad\Models\CasoUso;
use Modules\Paquete1Seguridad\Models\Rol;
use Modules\Paquete1Seguridad\Models\Autenticacion;
use Modules\Paquete1Seguridad\Models\PermisoRol;
use Modules\Paquete2Usuarios\Models\Persona;
use Modules\Paquete3Configuracion\Models\Categoria;
use Modules\Paquete3Configuracion\Models\Producto;
use Illuminate\Support\Facades\Hash;

class SaborXpressProyectSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ESTRUCTURA DE PAQUETES
        $p1 = Paquete::updateOrCreate(['codigo' => 'P1'], ['nombre' => 'Paquete 1: Seguridad']);
        $p2 = Paquete::updateOrCreate(['codigo' => 'P2'], ['nombre' => 'Paquete 2: Usuarios']);
        $p3 = Paquete::updateOrCreate(['codigo' => 'P3'], ['nombre' => 'Paquete 3: Configuración']);
        $p4 = Paquete::updateOrCreate(['codigo' => 'P4'], ['nombre' => 'Paquete 4: Inventarios']);
        $p5 = Paquete::updateOrCreate(['codigo' => 'P5'], ['nombre' => 'Paquete 5: Ventas']);

        // 2. CASOS DE USO (Fidelidad Total)
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
            CasoUso::updateOrCreate(['codigo' => $c[1]], ['nombre' => $c[2], 'id_paquete' => $paqId, 'es_crud' => $c[3]]);
        }

        // 3. ROLES
        $adminRol = Rol::updateOrCreate(['nombre' => 'Admin']);
        $cajeroRol = Rol::updateOrCreate(['nombre' => 'Cajero']);
        $cocineroRol = Rol::updateOrCreate(['nombre' => 'Cocinero']);
        $clienteRol = Rol::updateOrCreate(['nombre' => 'Cliente']);

        // Asignar todos los permisos al Admin
        foreach (CasoUso::all() as $cu) {
            PermisoRol::updateOrCreate(
                ['id_rol' => $adminRol->id, 'id_caso_uso' => $cu->id],
                ['puede_ver' => true, 'puede_crear' => true, 'puede_editar' => true, 'puede_eliminar' => true]
            );
        }

        // 4. PERSONAS Y USUARIOS (Migración Real de Laragon)
        $personasData = [
            ['id' => 1, 'nombre' => 'Bernardo Admin', 'telefono' => '70011223', 'correo' => 'admin@saborxpress.com', 'rol_id' => $adminRol->id],
            ['id' => 2, 'nombre' => 'Juan Cajero', 'telefono' => '60055443', 'correo' => 'cajero@saborxpress.com', 'rol_id' => $cajeroRol->id],
            ['id' => 7, 'nombre' => 'Katering Cairo', 'telefono' => '12457896', 'correo' => 'Katering@gmail.com', 'rol_id' => $cajeroRol->id],
            ['id' => 4, 'nombre' => 'Bernardo Chavez', 'telefono' => '78593512', 'correo' => 'bernardochavez595@gmail.com', 'rol_id' => $cocineroRol->id],
        ];

        foreach ($personasData as $p) {
            $per = Persona::updateOrCreate(['id' => $p['id']], ['nombre' => $p['nombre'], 'telefono' => $p['telefono']]);
            Autenticacion::updateOrCreate(
                ['id_persona' => $per->id],
                ['correo' => $p['correo'], 'contrasena' => Hash::make('admin123'), 'id_rol' => $p['rol_id']]
            );
        }

        // 5. CATÁLOGO (Categorías y Productos Reales)
        $c1 = Categoria::updateOrCreate(['id' => 1], ['nombre' => 'Pollos a la Brasa']);
        $c2 = Categoria::updateOrCreate(['id' => 2], ['nombre' => 'Hamburguesas']);
        $c3 = Categoria::updateOrCreate(['id' => 3], ['nombre' => 'Salchipapas']);

        Producto::updateOrCreate(['id' => 1], ['nombre' => 'Cuarto de Pollo', 'precio_venta' => 25.0, 'id_categoria' => $c1->id, 'descripcion' => 'Pierna con papas y ensalada']);
        Producto::updateOrCreate(['id' => 2], ['nombre' => 'Hamburguesa Simple', 'precio_venta' => 15.5, 'id_categoria' => $c2->id, 'descripcion' => 'Carne, queso y salsas']);
        Producto::updateOrCreate(['id' => 3], ['nombre' => 'Salchipapa Simple', 'precio_venta' => 15.0, 'id_categoria' => $c3->id, 'descripcion' => 'Clásica con papas fritas']);

        $this->command->info('Base de datos SaborXpress (Full Data) lista para migración.');
    }
}
