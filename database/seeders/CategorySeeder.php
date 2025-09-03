<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electrónicos',
                'slug' => 'electronicos',
                'description' => 'Productos electrónicos y tecnología',
                'color' => '#007bff',
                'is_active' => true
            ],
            [
                'name' => 'Ropa y Accesorios',
                'slug' => 'ropa-accesorios',
                'description' => 'Vestimenta y accesorios de moda',
                'color' => '#28a745',
                'is_active' => true
            ],
            [
                'name' => 'Hogar y Jardín',
                'slug' => 'hogar-jardin',
                'description' => 'Artículos para el hogar y jardinería',
                'color' => '#ffc107',
                'is_active' => true
            ],
            [
                'name' => 'Deportes',
                'slug' => 'deportes',
                'description' => 'Equipos y accesorios deportivos',
                'color' => '#dc3545',
                'is_active' => true
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}
