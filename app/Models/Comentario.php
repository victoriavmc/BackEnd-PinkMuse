<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Comentario extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'comentarios';
    protected $fillable = ['texto','fecha','tipoRefencia','usuario_id', 'referencia_id'];
}