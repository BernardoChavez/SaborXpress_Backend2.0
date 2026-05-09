<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CU16: Gestión de Cajas
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('autenticacion', 'id_persona');
            $table->decimal('monto_apertura', 10, 2);
            $table->decimal('monto_cierre', 10, 2)->nullable();
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();
            $table->enum('estado', ['Abierta', 'Cerrada'])->default('Abierta');
            $table->timestamps();
        });

        // CU17/19: Ventas y Pedidos
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_caja')->constrained('cajas');
            $table->foreignId('id_usuario')->constrained('autenticacion', 'id_persona');
            $table->decimal('monto_total', 10, 2);
            $table->enum('metodo_pago', ['Efectivo', 'QR']);
            $table->string('codigo_qr', 100)->nullable();
            $table->enum('tipo_entrega', ['Mesa', 'Llevar']);
            $table->enum('estado', ['Pendiente', 'Pagado', 'Cancelado'])->default('Pendiente');
            $table->integer('nro_pedido'); // Para el letrero LED
            $table->timestamps();
        });

        // Detalle de Ventas
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('id_producto')->constrained('producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('cajas');
    }
};
