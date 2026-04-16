<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model {
    protected $table = 'producto';
    protected $fillable = ['nombre', 'descripcion', 'precio_venta', 'id_categoria'];

    public function categoria() {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }
}