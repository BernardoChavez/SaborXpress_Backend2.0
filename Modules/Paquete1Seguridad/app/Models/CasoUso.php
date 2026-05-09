<?php
namespace Modules\Paquete1Seguridad\Models;
use Illuminate\Database\Eloquent\Model;

class CasoUso extends Model {
    protected $table = 'casos_uso';
    protected $fillable = ['id_paquete', 'codigo', 'nombre', 'es_crud'];

    public function paquete() {
        return $this->belongsTo(Paquete::class, 'id_paquete');
    }
}
