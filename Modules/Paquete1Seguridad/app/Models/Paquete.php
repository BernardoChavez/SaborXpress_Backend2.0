<?php
namespace Modules\Paquete1Seguridad\Models;
use Illuminate\Database\Eloquent\Model;

class Paquete extends Model {
    protected $table = 'paquetes';
    protected $fillable = ['nombre', 'codigo'];

    public function casosUso() {
        return $this->hasMany(CasoUso::class, 'id_paquete');
    }
}
