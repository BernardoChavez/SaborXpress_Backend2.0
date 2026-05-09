<?php
namespace Modules\Paquete1Seguridad\Models;
use Illuminate\Database\Eloquent\Model;

class PermisoRol extends Model {
    protected $table = 'permisos_rol';
    protected $fillable = ['id_rol', 'id_caso_uso', 'puede_ver', 'puede_crear', 'puede_editar', 'puede_eliminar'];

    public function casoUso() {
        return $this->belongsTo(CasoUso::class, 'id_caso_uso');
    }
}
