<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'credit_limit',
        'balance',
        'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Calculated spendings helper
    public function getTotalSpentAttribute()
    {
        return $this->orders()->sum('total_amount');
    }
}
