<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'float',
        'active' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_modifier');
    }
}
