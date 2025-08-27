<?php

require_once 'vendor/autoload.php';

// Configurar la aplicación Laravel para usar en consola
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;

echo "=== PRÁCTICA DE MODELOS ELOQUENT ===\n\n";



Category::firstOrCreate([
    'name' => 'Electronica',
    'slug' => 'electronica',
    'description' => 'Artículos y gadgets electrónicos',
    'color' => 'blue',
    'is_active' => true
]);

Category::firstOrCreate([
    'name' => 'Ropa',
    'slug' => 'ropa',
    'description' => 'Prendas de vestir y accesorios',
    'color' => 'red',
    'is_active' => true
]);

Category::firstOrCreate([
    'name' => 'Hogar',
    'slug' => 'hogar',
    'description' => 'Artículos para el hogar y jardín',
    'color' => 'green',
    'is_active' => true
]);

Category::firstOrCreate([
    'name' => 'Deportes',
    'slug' => 'deportes',
    'description' => 'Artículos deportivos y de fitness',
    'color' => 'orange',
    'is_active' => true
]);
$categorias = Category::all();


$cat1 = Category::where('slug', 'deportes')->first();
if($cat1){
    $cat1->is_active = false;
    $cat1->save();
}
foreach($categorias as $cat){
    echo "El nombre de la categoría es: " . $cat->name . "\n";
}
