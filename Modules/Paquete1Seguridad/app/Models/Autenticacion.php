<?php

namespace Modules\Paquete1Seguridad\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Paquete2Usuarios\Models\Persona;

class Autenticacion extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'autenticacion';
    protected $primaryKey = 'id_persona';
    public $incrementing = false;
    protected $fillable = ['id_persona', 'correo', 'contrasena', 'id_rol', 'intentos_fallidos', 'bloqueado_hasta'];
    protected $hidden = ['contrasena'];
    protected $appends = ['id'];

    public function getAuthPassword() {
        return $this->contrasena;
    }

    // --- AÑADE ESTA RELACIÓN AQUÍ ---
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function getIdAttribute()
    {
        return $this->attributes['id_persona'] ?? null;
    }
}
