<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingInput extends Model
{
    protected $fillable = ['wa_id', 'amount', 'category_name', 'step'];
}
