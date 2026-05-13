<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'admins';

    protected $fillable = ['username', 'password', 'nama'];

    protected $hidden = ['password'];

    // Gunakan guard 'admin'
    protected $guard = 'admin';
}
