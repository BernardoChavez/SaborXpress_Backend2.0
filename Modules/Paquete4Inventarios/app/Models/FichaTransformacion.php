<?php

namespace Modules\Paquete4Inventarios\Models;

use Illuminate\Database\Eloquent\Model;

class FichaTransformacion extends Model
{
    protected $table = 'fichas_transformacion';
    protected $fillable = ['id_bruto', 'id_procesado', 'cantidad_bruto', 'cantidad_procesado'];

    public function bruto()
    {
        return $this->belongsTo(InventarioBruto::class, 'id_bruto');
    }

    public function procesado()
    {
        return $this->belongsTo(InventarioProcesado::class, 'id_procesado');
    }
}
