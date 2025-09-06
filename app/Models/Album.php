<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Album extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'albums';
    protected $fillable = ['artista','fecha','imagenPrincipal','nombre', 'redSocial','canciones'];

}