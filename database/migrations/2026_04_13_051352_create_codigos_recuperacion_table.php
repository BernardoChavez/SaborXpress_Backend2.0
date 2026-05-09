<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('codigos_recuperacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_persona');
            $table->string('codigo', 6);
            $table->timestamp('expira_el');
            $table->timestamps();
            
            $table->foreign('id_persona')->references('id')->on('persona')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('codigos_recuperacion'); }
};
