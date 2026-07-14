<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'category_id', 'amount', 'description', 'date', 'source', 'wa_id', 'user_code',
        'is_recurring', 'recurring_interval', 'next_date', 'parent_id'
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'amount' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }
}
