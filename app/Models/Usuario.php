<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Usuario extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'usuarios';
    protected $fillable = ['nombre', 'apellido', 'nacionalidad', 'fechaNacimiento', 'correo', 'password', 'rol', 'perfil', 'preferenciaNotificacion','estado'];

    // Relación con Rol usando el campo 'rol' como identificador
    public function rolRelacion()
    {
        return $this->belongsTo(Rol::class, 'rol', 'rol');
    }
}