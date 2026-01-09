<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends User
{
    protected $table = 'users';
    protected $guard_name = 'web';
}
