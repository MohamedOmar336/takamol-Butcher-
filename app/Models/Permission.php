<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['slug', 'name_en', 'name_ar'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
