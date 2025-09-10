<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
     use HasFactory;

     protected $fillable = [
        'name',
        'description',
        'image_url',
        'price',
        'weight',
        'stock',
        'is_active',
        'category_id'
    ];
     // Casting de tipos de datos
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
