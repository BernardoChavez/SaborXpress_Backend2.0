<?php
 
namespace Modules\Paquete5Ventas\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Comanda extends Model
{
    protected $table = 'comandas';
    protected $fillable = ['id_venta', 'estado', 'area'];
 
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
}
