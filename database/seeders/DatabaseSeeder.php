<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Solo llamamos al Seeder Maestro que ya contiene TODO (Paquetes, Roles, Usuarios y Productos reales)
        $this->call(SaborXpressProyectSeeder::class);
    }
}