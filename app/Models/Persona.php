<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Persona extends Model
{
    use HasApiTokens;

    protected $table = 'persona';
    protected $fillable = ['nombre', 'telefono'];

    public function autenticacion() {
        return $this->hasOne(Autenticacion::class, 'id_persona');
    }
}