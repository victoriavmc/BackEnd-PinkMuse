<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Evento extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'eventos';
    protected $fillable = ['nombreEvento','nombreLugar','direccion','fecha','hora','artistasExtras','imagenPrincipal','estado','entradas', 'url'];
}
