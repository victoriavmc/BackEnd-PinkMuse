<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $collection = 'password_reset_tokens';
    protected $fillable = ['correo', 'token', 'created_at'];
    protected $primaryKey = '_id';
    public $timestamps = false;
}