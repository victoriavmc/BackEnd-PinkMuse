<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Reaccion extends Model
{
    public const TYPES = ['like', 'love', 'wow', 'angry', 'dislike'];

    protected $connection = 'mongodb';
    protected $collection = 'reacciones';

    protected $fillable = [
        'tipo',
        'tipoReferencia',
        'referencia_id',
        'usuario_id',
    ];

    public $timestamps = true;
}
