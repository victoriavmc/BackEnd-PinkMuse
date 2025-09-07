<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Accion extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'accions';
    protected $fillable = [
        'tipo',
        'causa',
        'fecha',
        'tipoReferencia',
        'referencia_id',
        'usuario_id',
    ];
}
