<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Noticia extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'noticias';
    protected $fillable = ['tipoActividad','titulo','descripcion','imagenPrincipal', 'imagenes','fecha','habilitarAcciones','habilitarComentarios'];
}
