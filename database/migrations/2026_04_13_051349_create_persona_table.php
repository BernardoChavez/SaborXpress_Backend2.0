<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('persona', function (Blueprint $table) {
            $table->id(); // Aquí el nombre real es "id"
            $table->string('nombre', 100);
            $table->string('telefono', 20)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('persona'); }
};