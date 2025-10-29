<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Comentario extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'comentarios';

    protected $fillable = [
        'texto',
        'fecha',
        'tipoReferencia',
        'usuario_id',
        'referencia_id',
        'meta',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'meta' => 'array',
    ];
}
