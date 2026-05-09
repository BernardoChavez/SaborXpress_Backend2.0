<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->timestamps();
        });

        Schema::create('paquetes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 20)->unique();
            $table->timestamps();
        });

        Schema::create('casos_uso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_paquete')->constrained('paquetes')->onDelete('cascade');
            $table->string('codigo', 20);
            $table->string('nombre', 100);
            $table->boolean('es_crud')->default(false);
            $table->timestamps();
        });

        Schema::create('permisos_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rol')->constrained('roles')->onDelete('cascade');
            $table->foreignId('id_caso_uso')->constrained('casos_uso')->onDelete('cascade');
            $table->boolean('puede_ver')->default(false);
            $table->boolean('puede_crear')->default(false);
            $table->boolean('puede_editar')->default(false);
            $table->boolean('puede_eliminar')->default(false);
            $table->timestamps();
            $table->unique(['id_rol', 'id_caso_uso']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('permisos_rol');
        Schema::dropIfExists('casos_uso');
        Schema::dropIfExists('paquetes');
        Schema::dropIfExists('roles');
    }
};
