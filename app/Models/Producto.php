<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Producto extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'productos';
    protected $fillable = ['nombre', 'imagenPrincipal', 'category', 'descripcion', 'precio', 'estado', 'stock', 'habilitarAcciones', 'habilitarComentarios'];

    // Nombre del producto → MAYÚSCULAS
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper($value);
    }
}
