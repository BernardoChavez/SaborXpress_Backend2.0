<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Autenticacion extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'autenticacion';
    protected $primaryKey = 'id_persona';
    public $incrementing = false;
    protected $fillable = ['id_persona', 'correo', 'contrasena', 'tipo_usuario'];
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

    public function getIdAttribute()
    {
        return $this->attributes['id_persona'] ?? null;
    }
}