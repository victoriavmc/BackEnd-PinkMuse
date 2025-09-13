<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    //
    protected $connection = 'mongodb';
    protected $collection = 'usuarios';
    protected $fillable = ['nombre', 'apellido', 'nacionalidad', 'fechaNacimiento', 'correo', 'password', 'rol', 'perfil', 'preferenciaNotificacion', 'estado'];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    // Relación con Rol usando el campo 'rol' como identificador
    public function rolRelacion()
    {
        return $this->belongsTo(Rol::class, 'rol', 'rol');
    }
}
