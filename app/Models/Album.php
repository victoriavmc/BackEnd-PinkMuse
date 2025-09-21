<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Album extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'albums';
    protected $fillable = ['artista','fecha','imagenPrincipal','nombre', 'redSocial','canciones'];

  // Nombre del álbum → Capitalizado
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = ucwords(strtolower($value));
    }
}