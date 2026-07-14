<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'emoji', 'color', 'budget', 'is_default'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'budget' => 'integer',
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
