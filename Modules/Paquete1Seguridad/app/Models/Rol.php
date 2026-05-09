<?php
namespace Modules\Paquete1Seguridad\Models;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model {
    protected $table = 'roles';
    protected $fillable = ['nombre'];

    public function permisos() {
        return $this->hasMany(PermisoRol::class, 'id_rol');
    }
}
