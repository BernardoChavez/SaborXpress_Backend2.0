<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Paquete3Configuracion\Models\Categoria;
use Modules\Paquete3Configuracion\Models\Producto;
use Modules\Paquete1Seguridad\Models\Rol;
use Modules\Paquete2Usuarios\Models\Persona;
use Modules\Paquete1Seguridad\Models\Autenticacion;

$data = [
    'categorias' => Categoria::all(),
    'productos' => Producto::all(),
    'roles' => Rol::all(),
    'personas' => Persona::all(),
    'usuarios' => Autenticacion::all(),
];

echo json_encode($data, JSON_PRETTY_PRINT);
