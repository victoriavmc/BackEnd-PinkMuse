<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Comprobante extends Model
{
    //

    protected $connection = 'mongodb';
    protected $collection = 'comprobantes';
    protected $fillable = ['numeroComprobante', 'fecha', 'usuario_id', 'datosPago', 'productos', 'total', 'estado'];
}