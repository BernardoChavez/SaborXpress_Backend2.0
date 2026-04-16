<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bitacora', function (Blueprint $table) {
            if (Schema::hasColumn('bitacora', 'detalles')) {
                $table->dropColumn('detalles');
            }

            if (Schema::hasColumn('bitacora', 'ip_address')) {
                $table->renameColumn('ip_address', 'ip');
            }

            if (!Schema::hasColumn('bitacora', 'accion_detalle')) {
                $table->string('accion_detalle')->nullable()->after('accion');
            }

            if (!Schema::hasColumn('bitacora', 'fecha')) {
                $table->date('fecha')->nullable()->after('ip');
            }

            if (!Schema::hasColumn('bitacora', 'hora_inicio')) {
                $table->time('hora_inicio')->nullable()->after('fecha');
            }

            if (!Schema::hasColumn('bitacora', 'hora_cierre')) {
                $table->time('hora_cierre')->nullable()->after('hora_inicio');
            }
        });

        DB::table('bitacora')
            ->whereNull('accion_detalle')
            ->update(['accion_detalle' => 'Accion registrada']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacora', function (Blueprint $table) {
            if (Schema::hasColumn('bitacora', 'hora_cierre')) {
                $table->dropColumn('hora_cierre');
            }

            if (Schema::hasColumn('bitacora', 'hora_inicio')) {
                $table->dropColumn('hora_inicio');
            }

            if (Schema::hasColumn('bitacora', 'fecha')) {
                $table->dropColumn('fecha');
            }

            if (Schema::hasColumn('bitacora', 'accion_detalle')) {
                $table->dropColumn('accion_detalle');
            }

            if (Schema::hasColumn('bitacora', 'ip')) {
                $table->renameColumn('ip', 'ip_address');
            }

            if (!Schema::hasColumn('bitacora', 'detalles')) {
                $table->text('detalles')->nullable()->after('accion');
            }
        });
    }
};
