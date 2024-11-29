<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivePlayer extends Model
{
    protected $fillable = ['name', 'streak'];
}
