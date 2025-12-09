<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{

    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id'];  
    public $timestamps = false;

}
