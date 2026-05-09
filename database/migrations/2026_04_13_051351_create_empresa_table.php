<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('nit', 50)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 100)->nullable();
            $table->string('moneda', 10)->default('Bs.');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('empresa'); }
};
