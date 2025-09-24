# Laravel: Factories y Seeders - Sembrado de la Base de Datos

## Información del Taller

**Nivel:** Intermedio
**Requisitos previos:** Haber completado el tutorial `25-08-27-STORAGE.md`
**Duración estimada:** 2 horas

## Objetivos de Aprendizaje

Al finalizar este taller, los estudiantes serán capaces de:

- Entender el concepto y propósito de Seeders y Factories
- Crear Seeders que lean datos desde archivos JSON
- Desarrollar Factories usando Faker para generar datos realistas
- Implementar relaciones entre modelos en Factories
- Crear datos de prueba complejos y coherentes
- Aplicar buenas prácticas en la generación de datos ficticios

## Introducción a Seeders

### ¿Qué son los Seeders?

Los **Seeders** son clases especializadas que nos permiten sembrar la base de datos con datos iniciales o de prueba de manera automática y reproducible.

#### Características principales:

- Permiten insertar datos de forma masiva
- Son útiles para datos iniciales del sistema
- Facilitan la creación de entornos de prueba consistentes
- Se pueden ejecutar múltiples veces de forma segura
- Soportan diferentes fuentes de datos (arrays, JSON, CSV, etc.)

#### ¿Cuándo usar Seeders?

- **Datos maestros**: Categorías, roles, configuraciones
- **Datos de prueba**: Para desarrollo y testing
- **Datos iniciales**: Usuarios administrador, configuraciones por defecto
- **Demostración**: Para mostrar funcionalidades a clientes

### Creando nuestro primer Seeder: CategoriesSeeder

Vamos a analizar el seeder de categorías que lee datos desde un archivo JSON:

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Leer las categorías desde un archivo JSON
        $json = file_get_contents(database_path('seeders/categories.json'));
        $categories = json_decode($json, true);

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']], // Criterio de búsqueda
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'color' => $category['color'],
                    'is_active' => $category['is_active']
                ]
            );
        }
    }
}
```

#### Explicación del código:

1. **`file_get_contents(database_path('seeders/categories.json'))`**: Lee el archivo JSON desde la carpeta de seeders
2. **`json_decode($json, true)`**: Convierte el JSON a array asociativo PHP
3. **`updateOrCreate()`**: Busca por slug, si existe actualiza, si no existe crea

#### Estructura del archivo JSON (`categories.json`):

```json
[
    {
        "slug": "electronics",
        "name": "Electronics",
        "description": "Electronic devices and gadgets",
        "color": "#FF5733",
        "is_active": true
    },
    {
        "slug": "clothing",
        "name": "Clothing", 
        "description": "Apparel and accessories",
        "color": "#33FF57",
        "is_active": true
    }
]
```

### Ejecutando Seeders

```bash
# Crear un seeder
php artisan make:seeder CategoriesSeeder

# Ejecutar un seeder específico
php artisan db:seed --class=CategoriesSeeder

# Ejecutar todos los seeders
php artisan db:seed

# Refrescar base de datos y ejecutar seeders
php artisan migrate:fresh --seed
```

### Registrar Seeders en DatabaseSeeder

Para que los seeders se ejecuten automáticamente, debemos registrarlos:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoriesSeeder::class,
            // CustomerSeeder::class,
            // ProductSeeder::class,
        ]);
    }
}
```

## Introducción a Factories

### ¿Qué son las Factories?

Las **Factories** son clases que definen cómo generar datos ficticios pero realistas para nuestros modelos usando la librería **Faker**.

#### Características principales:

- Generan datos realistas automáticamente
- Son reutilizables y configurables
- Integran perfectamente con Faker
- Permiten crear estados específicos del modelo
- Soportan relaciones entre modelos

#### ¿Por qué usar Factories?

- **Eficiencia**: Generan miles de registros rápidamente
- **Realismo**: Datos que parecen reales
- **Consistencia**: Misma estructura, datos variables
- **Testing**: Datos controlados para pruebas
- **Desarrollo**: Ambiente rico en datos desde el inicio
- **Producción**: Encapsulan lógica compleja de creación de modelos

### Introducción a Faker

**Faker** es una librería PHP que genera datos falsos pero realistas para testing y desarrollo.

#### Tipos de datos que puede generar Faker:

```php
// Datos personales
$faker->firstName()          // "María"
$faker->lastName()           // "González"
$faker->name()               // "Juan Carlos Rodríguez"
$faker->email()              // "juan@example.com"
$faker->safeEmail()          // "maria@example.org"

// Números y fechas
$faker->numberBetween(1, 100)    // 42
$faker->randomFloat(2, 0, 1000)  // 567.89
$faker->date()                   // "1995-08-15"
$faker->dateTimeBetween('-30 years', 'now') // DateTime object

// Texto
$faker->word()               // "voluptatem"
$faker->sentence()           // "Sit vitae voluptatem aut."
$faker->paragraph()          // Párrafo completo
$faker->text(200)            // Texto de 200 caracteres

// Internet y tecnología
$faker->url()                // "http://www.example.com"
$faker->imageUrl()           // "https://via.placeholder.com/640x480"
$faker->ipv4()               // "192.168.1.1"
$faker->userAgent()          // User agent del navegador

// Direcciones
$faker->address()            // "Calle Falsa 123, Springfield"
$faker->city()               // "Madrid"
$faker->country()            // "España"
$faker->phoneNumber()        // "+34 666 555 444"

// Comercio
$faker->company()            // "Tech Solutions S.L."
$faker->creditCardNumber()   // "4532015112830366"

// Utilidades
$faker->boolean()            // true/false
$faker->boolean(70)          // 70% probabilidad de true
$faker->randomElement(['A', 'B', 'C'])  // Elemento aleatorio del array
```

### Creando el Factory de Customer

Analicemos el CustomerFactory existente:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'birth_date' => $this->faker->date(),
            'is_premium' => $this->faker->boolean(),
        ];
    }
}
```

#### Explicación detallada:

1. **`$this->faker->firstName()`**: Genera nombres realistas
2. **`$this->faker->unique()->safeEmail()`**: Emails únicos y seguros
3. **`$this->faker->phoneNumber()`**: Números de teléfono con formato
4. **`$this->faker->date()`**: Fechas aleatorias
5. **`$this->faker->boolean()`**: 50% probabilidad de true/false

#### Mejorando el CustomerFactory con datos más realistas:

```php
public function definition(): array
{
    return [
        'first_name' => $this->faker->firstName(),
        'last_name' => $this->faker->lastName(),
        'email' => $this->faker->unique()->safeEmail(),
        'phone' => $this->faker->optional(0.8)->phoneNumber(), // 80% tienen teléfono
        'birth_date' => $this->faker->optional(0.7)->dateTimeBetween('-80 years', '-18 years'), // Adultos
        'is_premium' => $this->faker->boolean(20), // 20% son premium
    ];
}
```

### Creando el Factory de Product

Analicemos el ProductFactory con su generador personalizado:

```php
<?php

namespace Database\Factories;

use App\Models\Category;
use App\Utils\ProductNameGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        // Obtener categoría aleatoria
        $randomCategoryId = Category::query()->inRandomOrder()->first()->id ?? 1;
      
        // Generar datos coherentes de producto
        $productData = ProductNameGenerator::generateProductData();

        return [
            'name' => $productData['name'],
            'price' => $this->faker->randomFloat(2, 50, 100),
            'description' => $this->faker->text(150) . ' ' . $productData['description_suffix'],
            'image_url' => $this->faker->imageUrl(),
            'weight' => $this->faker->randomFloat(2, 0, 100),
            'stock' => $this->faker->numberBetween(0, 1000),
            'is_active' => $this->faker->boolean(80), // 80% activos
            'category_id' => $randomCategoryId,
        ];
    }
}
```

#### Aspectos destacados:

1. **Relaciones**:`Category::query()->inRandomOrder()->first()->id` obtiene categoría aleatoria
2. **Coherencia**: Usa`ProductNameGenerator` para nombres y descripciones coherentes
3. **Realismo**: Precios, pesos y stocks en rangos realistas
4. **Probabilidades**: 80% de productos activos

### Factories más allá de los Seeders

Los **Factories no son exclusivos de los seeders**. Son herramientas versátiles que pueden usarse en múltiples contextos:

#### 1. **Testing Unitario y de Integración**
```php
// En un test
public function test_customer_can_create_review()
{
    $customer = Customer::factory()->create();
    $product = Product::factory()->expensive()->create();
    
    $review = Review::factory()->for($customer)->for($product)->create();
    
    $this->assertInstanceOf(Review::class, $review);
}
```

#### 2. **Controladores - Lógica Compleja de Creación**
Los factories encapsulan lógica compleja que puede ser reutilizada desde controladores:

```php
// En lugar de duplicar lógica en el controlador:
public function createDemoAccount()
{
    // ❌ Lógica repetitiva y propensa a errores
    $customer = new Customer();
    $customer->first_name = 'Usuario';
    $customer->last_name = 'Demo';
    $customer->email = 'demo_' . time() . '@example.com';
    $customer->phone = '+34 600 000 000';
    $customer->is_premium = false;
    $customer->save();
    
    return $customer;
}

// ✅ Usar factory con estado específico:
public function createDemoAccount()
{
    return Customer::factory()->demo()->create();
}
```

#### 3. **Estados Específicos para Casos de Negocio**
```php
// En CustomerFactory.php - Estados para diferentes casos
public function demo(): static
{
    return $this->state(fn (array $attributes) => [
        'first_name' => 'Usuario',
        'last_name' => 'Demo',
        'email' => 'demo_' . time() . '@example.com',
        'phone' => '+34 600 000 000',
        'is_premium' => false,
    ]);
}

public function premium(): static
{
    return $this->state(fn (array $attributes) => [
        'is_premium' => true,
        'phone' => $this->faker->phoneNumber(), // Premium siempre tienen teléfono
    ]);
}

public function testUser(): static
{
    return $this->state(fn (array $attributes) => [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test+' . uniqid() . '@example.com',
        'is_premium' => false,
    ]);
}
```

#### 4. **Creación de Datos Relacionados desde Controladores**
```php
// Ejemplo: Crear producto con reseñas iniciales
public function createFeaturedProduct(array $productData)
{
    // Crear producto base
    $product = Product::factory()->create([
        'name' => $productData['name'],
        'price' => $productData['price'],
        'category_id' => $productData['category_id']
    ]);
    
    // Agregar algunas reseñas positivas automáticamente
    Review::factory()
          ->count(3)
          ->positive()
          ->verified()
          ->for($product)
          ->create();
    
    return $product;
}
```

#### 5. **Prototipos y Demos Rápidos**
```php
// Crear datos de demostración completos
public function setupStoreDemo()
{
    // Crear categorías básicas
    $electronics = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
    $clothing = Category::factory()->create(['name' => 'Clothing', 'slug' => 'clothing']);
    
    // Productos por categoría
    $products = collect();
    $products = $products->merge(
        Product::factory()->count(5)->electronics()->for($electronics)->create()
    );
    $products = $products->merge(
        Product::factory()->count(3)->for($clothing)->create()
    );
    
    // Clientes de diferentes tipos
    $customers = Customer::factory()->count(2)->premium()->create()
                ->merge(Customer::factory()->count(8)->create());
    
    // Reseñas realistas
    $products->each(function ($product) use ($customers) {
        $reviewCount = rand(1, 4);
        Review::factory()
              ->count($reviewCount)
              ->for($product)
              ->for($customers->random())
              ->create();
    });
    
    return [
        'categories' => [$electronics, $clothing],
        'products' => $products,
        'customers' => $customers
    ];
}
```

#### 6. **Casos de Uso en Producción**

**Creación de usuarios de prueba para clientes:**
```php
public function createTestEnvironmentForClient(int $clientId)
{
    // Crear 10 productos de muestra
    $products = Product::factory()
                      ->count(10)
                      ->inStock()
                      ->create(['client_id' => $clientId]);
    
    // 5 clientes de prueba
    $customers = Customer::factory()
                        ->count(5)
                        ->testUser()
                        ->create(['client_id' => $clientId]);
    
    return compact('products', 'customers');
}
```

**Onboarding automático:**
```php
public function completeUserOnboarding(User $user)
{
    // Crear perfil de cliente automáticamente
    $customer = Customer::factory()->create([
        'first_name' => $user->name,
        'email' => $user->email,
        'user_id' => $user->id
    ]);
    
    // Agregar productos sugeridos a wishlist
    $suggestedProducts = Product::factory()
                               ->count(3)
                               ->inStock()
                               ->create();
    
    return compact('customer', 'suggestedProducts');
}
```

#### **Ventajas de usar Factories en Controladores:**

1. **Reutilización**: La lógica de creación está centralizada
2. **Mantenibilidad**: Cambios en una sola ubicación
3. **Consistencia**: Mismo comportamiento en tests y producción
4. **Flexibilidad**: Estados configurables para diferentes casos
5. **Legibilidad**: Código más expresivo y fácil de entender

## Factory y Seeder de Reviews

### Análisis del modelo Review

Primero entendamos la estructura del modelo Review:

```php
class Review extends Model
{
    protected $fillable = [
        'product_id',
        'customer_id', 
        'rating',
        'comment',
        'is_verified_purchase',
        'reviewed_at',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
```

### Creando el ReviewFactory

Vamos a crear un factory complejo que genere reseñas realistas:

```bash
# Crear el factory
php artisan make:factory ReviewFactory
```

```php
<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $rating = $this->faker->numberBetween(1, 5);
        $comment = $this->generateCommentByRating($rating);
        $product = Product::query()->inRandomOrder()->first();
        $customer = Customer::query()->inRandomOrder()->first();
        $verified = $this->faker->boolean(75); // 75% compras verificadas
        $reviewedAt = $this->faker->dateTimeBetween('-2 years', 'now');

        return [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'rating' => $rating,
            'comment' => $comment,
            'is_verified_purchase' => $verified,
            'reviewed_at' => $reviewedAt,
        ];
    }

    /**
     * Genera comentarios coherentes según el rating
     */
    private function generateCommentByRating(int $rating): string
    {
        $comments = [
            1 => [
                'Muy decepcionante, no cumple las expectativas.',
                'Producto de mala calidad, no lo recomiendo.',
                'Llegó defectuoso y el servicio al cliente es pésimo.',
                'No vale la pena el precio, muy decepcionado.',
                'Calidad muy por debajo de lo esperado.'
            ],
            2 => [
                'No está mal pero esperaba más por el precio.',
                'Funciona pero tiene algunos defectos menores.',
                'Cumple lo básico, aunque podría ser mejor.',
                'Por el precio esperaba mejor calidad.',
                'Aceptable pero hay mejores opciones en el mercado.'
            ],
            3 => [
                'Producto correcto, cumple su función.',
                'Está bien para el precio que tiene.',
                'Sin más, hace lo que promete.',
                'Calidad-precio aceptable, aunque mejorable.',
                'Producto estándar, nada extraordinario.'
            ],
            4 => [
                'Muy buen producto, lo recomiendo.',
                'Excelente calidad-precio, muy satisfecho.',
                'Cumple perfectamente las expectativas.',
                'Gran producto, muy contento con la compra.',
                'Muy buena calidad, llegó rápido y bien empacado.'
            ],
            5 => [
                '¡Excelente! Superó todas mis expectativas.',
                'Producto fantástico, calidad premium.',
                'Perfecto en todos los aspectos, 100% recomendado.',
                '¡Increíble! La mejor compra que he hecho.',
                'Calidad excepcional, volveré a comprar sin duda.'
            ]
        ];

        $ratingComments = $comments[$rating];
        return $this->faker->randomElement($ratingComments);
    }
}
```

### Creando el ReviewSeeder

```bash
# Crear el seeder
php artisan make:seeder ReviewSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;
        for ($i = 0; $i < 50; $i++) {
            $product = Product::query()->inRandomOrder()->first();
            $customer = Customer::query()->inRandomOrder()->first();

            // Verificar que no existe ya una reseña
            $exists = Review::query()
                ->where('product_id', $product->id)
                ->where('customer_id', $customer->id)
                ->exists();

            if (!$exists) {
                Review::factory()
                    ->create([
                        'product_id' => $product->id,
                        'customer_id' => $customer->id,
                    ]);
                    $count++;
            }
        }
        $this->command->info("Se crearon {$count} reseñas aleatorias.");
    }
}
```

## Prácticas para Estudiantes

### Ejercicio Práctico 1: Expandir CategoriesSeeder

**Objetivo**: Mejorar el seeder de categorías con más funcionalidades.

**Tarea**: Modifica el `CategoriesSeeder` para que:

1. Agregue un log de cuántas categorías fueron creadas vs actualizadas
2. Valide que el archivo JSON existe antes de procesarlo
3. Maneje errores si el JSON está mal formado

**Solución sugerida**:

```php
public function run(): void
{
    $filePath = database_path('seeders/categories.json');
  
    // Verificar que el archivo existe
    if (!file_exists($filePath)) {
        $this->command->error("❌ Archivo {$filePath} no encontrado");
        return;
    }

    try {
        $json = file_get_contents($filePath);
        $categories = json_decode($json, true);
      
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("❌ Error al parsear JSON: " . json_last_error_msg());
            return;
        }

        $created = 0;
        $updated = 0;

        foreach ($categories as $categoryData) {
            $category = Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'color' => $categoryData['color'],
                    'is_active' => $categoryData['is_active']
                ]
            );

            if ($category->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info("✅ Categorías procesadas: {$created} creadas, {$updated} actualizadas");

    } catch (\Exception $e) {
        $this->command->error("❌ Error inesperado: " . $e->getMessage());
    }
}
```

### Ejercicio Práctico 2: Factory Avanzado de Product

**Objetivo**: Crear variantes más específicas del ProductFactory para usar tanto en seeders como en controladores.

**Tarea**: Agregar los siguientes estados al `ProductFactory`:

- `expensive()`: Productos de lujo (precio > 500)
- `inStock()`: Productos con stock garantizado (> 10)
- `electronics()`: Solo productos de electrónicos
- `onSale()`: Productos en oferta (con descuento)
- `featured()`: Productos destacados para homepage
- `demo()`: Productos para demostraciones

**Esqueleto de solución**:

```php
// En ProductFactory.php, agregar estos métodos:

public function expensive(): static
{
    return $this->state(fn (array $attributes) => [
        'price' => $this->faker->randomFloat(2, 500, 2000),
    ]);
}

public function inStock(): static
{
    return $this->state(fn (array $attributes) => [
        'stock' => $this->faker->numberBetween(10, 500),
        'is_active' => true,
    ]);
}

public function electronics(): static
{
    return $this->state(function (array $attributes) {
        $electronicsCategory = Category::where('slug', 'electronics')->first();
        return [
            'category_id' => $electronicsCategory?->id ?? 1,
        ];
    });
}

public function onSale(): static
{
    return $this->state(function (array $attributes) {
        $originalPrice = $this->faker->randomFloat(2, 100, 500);
        $discountPercent = $this->faker->numberBetween(10, 50);
        $salePrice = $originalPrice * (1 - $discountPercent / 100);
      
        return [
            'price' => round($salePrice, 2),
        ];
    });
}

public function featured(): static
{
    return $this->state(fn (array $attributes) => [
        'is_active' => true,
        'stock' => $this->faker->numberBetween(50, 200),
        'price' => $this->faker->randomFloat(2, 80, 300),
    ]);
}

public function demo(): static
{
    return $this->state(function (array $attributes) {
        return [
            'name' => 'Producto Demo - ' . $this->faker->word(),
            'description' => 'Este es un producto de demostración para mostrar funcionalidades.',
            'price' => $this->faker->randomElement([19.99, 29.99, 49.99, 99.99]),
            'stock' => 100,
            'is_active' => true,
        ];
    });
}
```

### Ejercicio Práctico 3: Seeder Complejo

**Objetivo**: Crear un seeder que maneja dependencias y relaciones.

**Tarea**: Crea un `CompleteStoreSeeder` que:

1. Verifique y cree las categorías necesarias
2. Cree productos para cada categoría
3. Cree clientes
4. Cree reseñas realistas manteniendo coherencia

**Esqueleto**:

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Review;
use Illuminate\Database\Seeder;

class CompleteStoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏪 Configurando tienda completa...');
      
        // Paso 1: Categorías
        $this->createCategories();
      
        // Paso 2: Productos por categoría  
        $this->createProducts();
      
        // Paso 3: Clientes variados
        $this->createCustomers();
      
        // Paso 4: Reseñas coherentes
        $this->createCoherentReviews();
      
        $this->showSummary();
    }

    private function createCategories(): void
    {
        // Tu implementación aquí
    }

    private function createProducts(): void
    {
        // Crear productos específicos por categoría
        // Usar factories con estados específicos
    }

    private function createCustomers(): void
    {
        // Mezcla de clientes premium y regulares
    }

    private function createCoherentReviews(): void
    {
        // Productos caros → menos reseñas pero mejores ratings
        // Productos baratos → más reseñas, ratings variados
    }

    private function showSummary(): void
    {
        $this->command->table(
            ['Modelo', 'Cantidad'],
            [
                ['Categorías', Category::count()],
                ['Productos', Product::count()],
                ['Clientes', Customer::count()],
                ['Reseñas', Review::count()],
            ]
        );
    }
}
```

### Ejercicio Práctico 4: Factory para Casos de Negocio

**Objetivo**: Crear factories específicos para casos reales de controladores.

**Tarea A**: Agrega estos estados al `CustomerFactory` para diferentes escenarios de negocio:
- `vip()`: Clientes VIP con datos premium
- `testAccount()`: Cuentas de prueba para demos
- `international()`: Clientes internacionales
- `corporate()`: Clientes corporativos

**Tarea B**: Crea métodos en factories que puedan ser llamados desde controladores para:
- Crear un "starter pack" de productos para nuevos clientes
- Generar datos de demostración para presentaciones
- Crear usuarios de prueba con historial de compras

### Ejercicio Práctico 5: Faker Personalizado

**Objetivo**: Crear datos más realistas con Faker personalizado.

**Tarea**: Crea un `Provider` personalizado de Faker para generar datos específicos de productos tech.

**Archivo**: `database/factories/TechProvider.php`

```php
<?php

namespace Database\Factories;

use Faker\Provider\Base;

class TechProvider extends Base
{
    protected static $brands = [
        'TechPro', 'InnovaTech', 'SmartDevices', 'ElectroMax', 
        'DigitalPlus', 'NextGen', 'ProTech', 'UltraDevices'
    ];

    protected static $techWords = [
        'Smart', 'Pro', 'Ultra', 'Max', 'Elite', 'Premium', 
        'Advanced', 'Digital', 'Wireless', 'Bluetooth'
    ];

    protected static $categories = [
        'Smartphone', 'Laptop', 'Tablet', 'Headphones', 
        'Speaker', 'Monitor', 'Keyboard', 'Mouse'
    ];

    public function techBrand(): string
    {
        return static::randomElement(static::$brands);
    }

    public function techProductName(): string
    {
        $brand = $this->techBrand();
        $word = static::randomElement(static::$techWords);
        $category = static::randomElement(static::$categories);
      
        return "{$brand} {$category} {$word}";
    }

    public function techPrice(): float
    {
        // Precios realistas según categoría
        $basePrice = $this->generator->numberBetween(50, 200);
        $multiplier = $this->generator->randomFloat(1, 1.2, 3.5);
      
        return round($basePrice * $multiplier, 2);
    }
}
```

**Uso en Factory**:

```php
// En ProductFactory.php
public function definition(): array
{
    $this->faker->addProvider(new \Database\Factories\TechProvider($this->faker));
  
    return [
        'name' => $this->faker->techProductName(),
        'price' => $this->faker->techPrice(),
        // ... resto de campos
    ];
}
```

## Mejores Prácticas y Consejos

### Mejores Prácticas para Seeders

1. **Usar `updateOrCreate()` para evitar duplicados**:

```php
Category::updateOrCreate(
    ['slug' => $data['slug']], // Criterio único
    $data // Datos a crear/actualizar
);
```

2. **Verificar dependencias antes de crear**:

```php
if (Category::count() === 0) {
    $this->command->error('Ejecuta CategoriesSeeder primero');
    return;
}
```

3. **Proporcionar feedback al usuario**:

```php
$this->command->info('✅ Creando productos...');
$this->command->error('❌ Error al crear datos');
```

4. **Manejar grandes volúmenes eficientemente**:

```php
// En lugar de crear uno por uno
foreach ($data as $item) {
    Model::create($item); // Lento
}

// Mejor: inserción masiva
Model::insert($data); // Rápido pero sin eventos
// o
DB::table('models')->insert($data); // Más rápido aún
```

### Mejores Prácticas para Factories

1. **Estados específicos para casos de uso**:

```php
// En seeders
User::factory()->admin()->create();
Product::factory()->featured()->expensive()->create();

// En controladores
$demoUser = Customer::factory()->demo()->create();
$testProducts = Product::factory()->count(5)->inStock()->create();
```

2. **Usar callbacks para lógica compleja**:

```php
public function configure(): static
{
    return $this->afterCreating(function (Product $product) {
        // Crear automáticamente algunas reseñas
        Review::factory()
              ->count(rand(1, 5))
              ->for($product)
              ->create();
    });
}
```

3. **Relaciones eficientes**:

```php
// Crear con relación existente
Review::factory()->for($product)->create();

// Crear con relación nueva
Review::factory()->for(Product::factory())->create();
```

4. **Usar factories en controladores para lógica compleja**:

```php
// ❌ Evitar lógica repetitiva en controladores
public function createTestData() {
    $customer = new Customer();
    $customer->first_name = 'Test';
    // ... más código repetitivo
}

// ✅ Usar factory con estado específico
public function createTestData() {
    return Customer::factory()->testAccount()->create();
}
```

### Comandos Útiles para Development

```bash
# Crear factory junto con modelo
php artisan make:model Product -f

# Crear factory, seeder y migración
php artisan make:model Review -fms

# Refrescar BD con seeders
php artisan migrate:fresh --seed

# Solo seeders sin migrar
php artisan db:seed

# Seeder específico
php artisan db:seed --class=ReviewSeeder

# Verificar estado de migraciones
php artisan migrate:status
```

## Resumen del Taller

### Lo que hemos aprendido:

1. **Seeders**:

   - Sembrar BD con datos iniciales
   - Leer desde archivos JSON/CSV
   - Manejar duplicados con`updateOrCreate()`
   - Proporcionar feedback al usuario
2. **Factories**:

   - Generar datos ficticios realistas
   - Usar Faker para diferentes tipos de datos
   - Crear estados específicos del modelo
   - Manejar relaciones entre modelos
   - **Uso versátil**: No solo para seeders, también para controladores y testing
   - **Encapsulación**: Lógica compleja de creación centralizada
3. **Faker**:

   - Tipos de datos disponibles
   - Personalizar probabilidades
   - Crear providers personalizados
4. **Casos Complejos**:

   - Factories que respetan lógica de negocio
   - Seeders con dependencias
   - Datos coherentes entre modelos

### Recursos adicionales:

- [Documentación oficial de Laravel Seeders](https://laravel.com/docs/seeding)
- [Documentación oficial de Laravel Factories](https://laravel.com/docs/eloquent-factories)
- [Faker PHP Documentation](https://fakerphp.github.io/)
- [Ejemplos de datos realistas con Faker](https://github.com/fzaninotto/Faker#formatters)
