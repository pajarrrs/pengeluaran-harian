<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaUser extends Model
{
    protected $fillable = ['wa_id', 'access_code'];
}
