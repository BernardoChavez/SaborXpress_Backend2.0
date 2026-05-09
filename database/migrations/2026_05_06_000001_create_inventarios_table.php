<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CU12: Inventario Bruto (Materia Prima)
        Schema::create('inventario_bruto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->decimal('stock', 10, 2)->default(0);
            $table->string('unidad_medida', 20); // sacos, kg, unidades
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->timestamps();
        });

        // CU13: Inventario Procesado (Insumos para cocina)
        Schema::create('inventario_procesado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->decimal('stock', 10, 2)->default(0);
            $table->string('unidad_medida', 20); // kg, porciones, presas
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->timestamps();
        });

        // CU38: Fichas de Transformación (Bruto -> Procesado)
        Schema::create('fichas_transformacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bruto')->constrained('inventario_bruto');
            $table->foreignId('id_procesado')->constrained('inventario_procesado');
            $table->decimal('cantidad_bruto', 10, 2);    // Ej: 1 (saco)
            $table->decimal('cantidad_procesado', 10, 2); // Ej: 50 (kg)
            $table->timestamps();
        });

        // CU38: Recetas (Procesado -> Producto Final)
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('producto');
            $table->foreignId('id_procesado')->constrained('inventario_procesado');
            $table->decimal('cantidad', 10, 2); // Cantidad de insumo procesado que consume el plato
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
        Schema::dropIfExists('fichas_transformacion');
        Schema::dropIfExists('inventario_procesado');
        Schema::dropIfExists('inventario_bruto');
    }
};
