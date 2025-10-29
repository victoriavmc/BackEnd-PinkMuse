<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Noticia extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'noticias';

    protected $fillable = [
        'tipoActividad',
        'titulo',
        'descripcion',
        'resumen',
        'imagenPrincipal',
        'imagenes',
        'fecha',
        'habilitacionAcciones',
        'habilitacionComentarios',
        'autor',
        'categoria',
        'fuente',
        'etiquetas',
    ];

    public function setTituloAttribute($value)
    {
        $this->attributes['titulo'] = ucwords(strtolower($value));
    }
}
