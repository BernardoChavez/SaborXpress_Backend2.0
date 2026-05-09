<?php

namespace Modules\Paquete4Inventarios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventarioBruto extends Model
{
    use HasFactory;

    protected $table = 'inventario_bruto';
    protected $fillable = ['nombre', 'stock', 'unidad_medida', 'stock_minimo'];
}
