<?php

namespace Modules\Paquete5Ventas\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Paquete1Seguridad\Models\Autenticacion;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $fillable = ['id_caja', 'id_usuario', 'monto_total', 'metodo_pago', 'codigo_qr', 'tipo_entrega', 'estado', 'nro_pedido'];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    public function usuario()
    {
        return $this->belongsTo(Autenticacion::class, 'id_usuario', 'id_persona');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'id_venta');
    }
}
