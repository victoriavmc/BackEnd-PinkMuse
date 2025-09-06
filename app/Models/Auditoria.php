<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
class Auditoria extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'auditorias';
    protected $fillable = ['accion','coleccion','fecha','datos', 'usuario_id'];

}
