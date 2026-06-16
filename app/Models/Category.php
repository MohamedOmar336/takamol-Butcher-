<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name_en', 'name_ar', 'slug'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
