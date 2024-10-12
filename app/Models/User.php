<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = [
        'nama_user', 
        'email', 
        'password', 
        'foto', 
        'role'
    ];

    protected $hidden = [
        'password'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();  // Mengambil ID user
    }

    public function getJWTCustomClaims()
    {
        return [];  // Tambahan claims kalau diperlukan
    }
}