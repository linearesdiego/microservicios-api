<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
     use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "price",
        "stock",
        "is_active",
    ];

     protected $dates = [
        'created_at',
        'updated_at'
    ];

     // Casting de tipos de datos
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
