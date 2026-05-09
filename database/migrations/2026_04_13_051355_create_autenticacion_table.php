<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('autenticacion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_persona')->primary();
            $table->string('correo', 100)->unique();
            $table->string('contrasena');
            $table->unsignedBigInteger('id_rol');
            $table->integer('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->timestamps();

            $table->foreign('id_rol')->references('id')->on('roles')->onDelete('cascade');

            // Referencia correcta a "id" en la tabla "persona"
            $table->foreign('id_persona')->references('id')->on('persona')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('autenticacion'); }
};