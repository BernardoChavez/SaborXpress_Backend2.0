<?php
namespace Modules\Paquete1Seguridad\Models;
use Illuminate\Database\Eloquent\Model;
use Modules\Paquete2Usuarios\Models\Persona;

class CodigoRecuperacion extends Model {
    protected $table = 'codigos_recuperacion';
    protected $fillable = ['id_persona', 'codigo', 'expira_el'];
    public $timestamps = true;

    public function persona() {
        return $this->belongsTo(Persona::class, 'id_persona');
    }
}
