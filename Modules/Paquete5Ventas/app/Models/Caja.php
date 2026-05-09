<?php

namespace Modules\Paquete5Ventas\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Paquete1Seguridad\Models\Autenticacion;

class Caja extends Model
{
    protected $table = 'cajas';
    protected $fillable = [
        'id_usuario', 
        'monto_apertura', 
        'monto_apertura_qr', 
        'monto_cierre', 
        'monto_cierre_qr', 
        'fecha_apertura', 
        'fecha_cierre', 
        'estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(Autenticacion::class, 'id_usuario', 'id_persona');
    }
}
