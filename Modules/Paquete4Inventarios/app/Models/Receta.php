<?php

namespace Modules\Paquete4Inventarios\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Paquete3Configuracion\Models\Producto;

class Receta extends Model
{
    protected $table = 'recetas';
    protected $fillable = ['id_producto', 'id_procesado', 'cantidad'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function procesado()
    {
        return $this->belongsTo(InventarioProcesado::class, 'id_procesado');
    }
}
