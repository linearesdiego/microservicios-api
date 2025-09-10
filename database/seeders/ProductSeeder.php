<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('slug', 'electronicos')->first();
        $clothing = Category::where('slug', 'ropa-accesorios')->first();
        $home = Category::where('slug', 'hogar-jardin')->first();
        $sports = Category::where('slug', 'deportes')->first();

        $products = [
            // Electrónicos
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Smartphone Apple con pantalla de 6.1 pulgadas',
                'price' => 999.99,
                'stock' => 50,
                'is_active' => true,
                'weight' => '0.2',
                'image_url' => 'https://example.com/images/iphone15pro.jpg',
                'category_id' => $electronics->id
            ],
            [
                'name' => 'Laptop HP Pavilion',
                'description' => 'Laptop con procesador Intel i7 y 16GB RAM',
                'price' => 799.99,
                'stock' => 25,
                'is_active' => true,
                'weight' => '0.2',
                'image_url' => 'https://example.com/images/iphone15pro.jpg',
                'category_id' => $electronics->id
            ],

            // Ropa
            [
                'name' => 'Camiseta Básica',
                'description' => 'Camiseta de algodón 100% en varios colores',
                'price' => 19.99,
                'stock' => 100,
                'is_active' => true,
                'weight' => '0.2',
                'image_url' => 'https://example.com/images/iphone15pro.jpg',
                'category_id' => $clothing->id
            ],
            [
                'name' => 'Jeans Clásicos',
                'description' => 'Pantalón jean de corte clásico',
                'price' => 49.99,
                'stock' => 75,
                'is_active' => true,
                'weight' => '0.2',
                'image_url' => 'https://example.com/images/iphone15pro.jpg',
                'category_id' => $clothing->id
            ],

            // Hogar
            [
                'name' => 'Aspiradora Robot',
                'description' => 'Aspiradora automática con WiFi',
                'price' => 299.99,
                'stock' => 20,
                'is_active' => true,
                'weight' => '0.2',
                'image_url' => 'https://example.com/images/iphone15pro.jpg',
                'category_id' => $home->id
            ],

            // Deportes
            [
                'name' => 'Balón de Fútbol',
                'description' => 'Balón oficial de fútbol profesional',
                'price' => 39.99,
                'stock' => 40,
                'weight' => '0.2',
                'image_url' => 'https://example.com/images/iphone15pro.jpg',
                'is_active' => true,
                'category_id' => $sports->id
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
