<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Accion extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'acciones';
    protected $fillable = ['tipo','causa','tipoRefencia','usuario_id', 'referencia_id'];
}
