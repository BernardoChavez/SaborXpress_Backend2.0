<?php
namespace Modules\Paquete3Configuracion\Models;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model {
    protected $table = 'categoria';
    protected $fillable = ['nombre'];
}
