<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RedSocial extends Model
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'red_socials';
    protected $fillable = ['nombre', 'url'];

}
