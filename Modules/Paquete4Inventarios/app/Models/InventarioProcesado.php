<?php

namespace Modules\Paquete4Inventarios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventarioProcesado extends Model
{
    use HasFactory;

    protected $table = 'inventario_procesado';
    protected $fillable = ['nombre', 'stock', 'unidad_medida', 'stock_minimo'];
}
