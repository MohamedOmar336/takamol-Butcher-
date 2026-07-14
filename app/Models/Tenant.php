<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    // Explicitly point to the central database connection
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'db_name',
        'store_type',
        'owner_email',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
