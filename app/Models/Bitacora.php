<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Paquete1Seguridad\Models\Autenticacion;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $fillable = [
        'id_usuario',
        'accion',
        'accion_detalle',
        'ip',
        'fecha',
        'hora_inicio',
        'hora_cierre',
    ];

    public function usuario()
    {
        return $this->belongsTo(Autenticacion::class, 'id_usuario', 'id_persona');
    }
}