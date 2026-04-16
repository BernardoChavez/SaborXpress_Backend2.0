<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('accion');
            $table->string('accion_detalle')->nullable();
            $table->string('ip', 45);
            $table->date('fecha')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_cierre')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_persona')->on('autenticacion')->onDelete('set null');
        });
    }
    public function down(): void { Schema::dropIfExists('bitacora'); }
};