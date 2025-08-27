<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        "firs_name",
        "last_name",
        "email",
        "phone",
        "birth_date",
        "is_premium"

    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_premium' => 'boolean',
    ];

}
