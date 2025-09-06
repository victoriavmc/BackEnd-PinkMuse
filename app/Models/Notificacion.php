<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notificacion extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'notificaciones';
    protected $fillable = ['tipo','mensaje','tipoRefencia','referencia_id'];
}
