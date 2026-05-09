<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CU20/22: Comandas Digitales
        Schema::create('comandas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')->constrained('ventas')->onDelete('cascade');
            $table->enum('estado', ['Pendiente', 'En preparación', 'Listo', 'Entregado', 'Anulado'])->default('Pendiente');
            $table->string('area', 50)->default('Cocina'); // Frituras, Bebidas, Parrilla, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comandas');
    }
};
