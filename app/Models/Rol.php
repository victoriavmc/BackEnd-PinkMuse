<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Rol extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'rols';
    protected $fillable = ['rol', 'permisos'];

}