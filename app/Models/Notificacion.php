<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notificacion extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'notificaciones';
    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensaje',
        'referencia_tipo',
        'referencia_id',
        'datos',
        'leida',
        'fecha',
    ];



    protected $attributes = [
        'datos' => [],
        'leida' => false,
    ];
}
