<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Evento extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'eventos';
    protected $fillable = ['nombreEvento','nombreLugar','direccion','fecha','hora','artistasExtras','imagenPrincipal','estado','entradas', 'url'];

     // Nombre del evento → Capitalizado
    public function setNombreEventoAttribute($value)
    {
        $this->attributes['nombreEvento'] = ucwords(strtolower($value));
    }

    // Nombre del lugar → Capitalizado
    public function setNombreLugarAttribute($value)
    {
        $this->attributes['nombreLugar'] = ucwords(strtolower($value));
    }

    // Direcion -> Capitalizado
    public function setDireccionAttribute($value)
    {
        $this->attributes['direccion'] = ucwords(strtolower($value));
    }
}