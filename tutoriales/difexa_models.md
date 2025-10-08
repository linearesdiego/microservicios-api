# Migraciones y Modelos Difexa

## Introducción

En este tutorial aprenderás a crear las migraciones y modelos Eloquent para un sistema de gestión de contenido Difexa. Trabajaremos con entidades principales, tablas pivot y enumeraciones (enums).

## ¿Qué son las Migraciones?

Las migraciones en Laravel son archivos PHP que definen la estructura de la base de datos de forma versionada. Permiten crear, modificar y eliminar tablas y campos de manera controlada y compartible entre desarrolladores.

## ¿Qué son los Enums?

Los enums (enumeraciones) son tipos de datos que permiten definir un conjunto fijo de valores posibles para un campo. Laravel soporta enums nativos de PHP 8.1+ y también mediante validaciones de base de datos.

## Comandos Artisan Básicos

### Crear Migraciones
```bash
php artisan make:migration create_nombre_tabla_table
```

### Crear Modelos
```bash
php artisan make:model NombreModelo
```

### Crear Modelo con Migración
```bash
php artisan make:model NombreModelo -m
```

### Ejecutar Migraciones
```bash
php artisan migrate
```

## Paso 1: Creación de Enums

Primero, creamos los enums que utilizaremos en nuestros modelos:

```php
<?php

namespace App\Enums;

enum PostType: string
{
    case ARTICLE = 'article';
    case VIDEO = 'video';
    case PODCAST = 'podcast';
    case IMAGE = 'image';
}
```

```php
<?php

namespace App\Enums;

enum MediaType: string
{
    case PHISICAL_SCREEN = 'phisical_screen';
    case SOCIAL_MEDIA = 'social_media';
    case EDITORIAL_PLATFORM = 'editorial_platform';
}
```

```php
<?php

namespace App\Enums;

enum ChannelType: string
{
    case DEPARTMENT = 'department';
    case INSTITUTE = 'institute';
    case SECRETARY = 'secretary';
    case CENTER = 'center';
}
```

```php
<?php

namespace App\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
```

## Paso 2: Migración para extender la tabla Users

Dado que ya tienes Sanctum instalado, la tabla `users` se mantendrá con la estructura básica de Sanctum. Según el diagrama, no se requieren campos adicionales:

```bash
# No necesitamos migración adicional para users
# La tabla users mantendrá la estructura estándar de Sanctum:
# - id: bigint PK
# - name: varchar(255)
# - email: varchar(255) UNIQUE
# - email_verified_at: timestamp NULL
# - password: varchar(255)
# - remember_token: varchar(100) NULL
# - created_at: timestamp
# - updated_at: timestamp
```

## Paso 3: Migración de Posts

```bash
php artisan make:migration create_posts_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PostType;
use App\Enums\PostStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->enum('type', array_column(PostType::cases(), 'value'))
                  ->default(PostType::ARTICLE->value);
            $table->enum('status', array_column(PostStatus::cases(), 'value'))
                  ->default(PostStatus::DRAFT->value);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'published_at']);
            $table->index(['type', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

## Paso 4: Migración de Channels

```bash
php artisan make:migration create_channels_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ChannelType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', array_column(ChannelType::cases(), 'value'));
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
```

## Paso 5: Migración de Media

```bash
php artisan make:migration create_media_table
```

```php 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MediaType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', array_column(MediaType::cases(), 'value'));
            $table->json('configuration')->nullable();
            $table->text('semantic_context')->nullable();
            $table->string('url_webhook')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
```

## Paso 6: Migración de Attachments

```bash
php artisan make:migration create_attachments_table
```

```php 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('disk')->default('public');
            $table->text('description')->nullable();
            $table->integer('download_count')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
```

## Paso 7: Tablas Pivot

### 7.1 Tabla Pivot: user_channels (Many-to-Many)

```bash
php artisan make:migration create_user_channels_table
```

```php 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'channel_id']);
            $table->index(['channel_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_channels');
    }
};
```

### 7.2 Tabla Pivot: post_channels (Many-to-Many)

```bash
php artisan make:migration create_post_channels_table
```

```php 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['post_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_channels');
    }
};
```

### 7.3 Tabla Pivot: post_medias (Many-to-Many)

```bash
php artisan make:migration create_post_medias_table
```

```php 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_medias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['post_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_medias');
    }
};
```

### 7.4 Tabla Pivot: channel_medias (Many-to-Many)

```bash
php artisan make:migration create_channel_medias_table
```

```php 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_medias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['channel_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_medias');
    }
};
```

## Paso 8: Modelos Eloquent

### 8.1 Modelo Post

```bash
php artisan make:model Post
```

```php 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\PostType;
use App\Enums\PostStatus;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'excerpt',
        'type',
        'status',
        'scheduled_at',
        'published_at',
    ];

    protected $casts = [
        'type' => PostType::class,
        'status' => PostStatus::class,
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // Relación con User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación Many-to-Many con Channels
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'post_channels')
                    ->withTimestamps();
    }

    // Relación Many-to-Many con Media
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'post_medias')
                    ->withTimestamps();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', PostStatus::PUBLISHED);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', PostStatus::SCHEDULED)
                    ->whereNotNull('scheduled_at');
    }

    public function scopeByType($query, PostType $type)
    {
        return $query->where('type', $type);
    }
}
```

### 8.2 Modelo Channel

```bash
php artisan make:model Channel
```

```php 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ChannelType;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
        'type' => ChannelType::class,
        'is_active' => 'boolean',
    ];

    // Relación Many-to-Many con Users
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_channels')
                    ->withPivot(['is_approved', 'approved_at'])
                    ->withTimestamps();
    }

    // Relación Many-to-Many con Posts
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_channels')
                    ->withTimestamps();
    }

    // Relación Many-to-Many con Media
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'channel_medias')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, ChannelType $type)
    {
        return $query->where('type', $type);
    }
}
```

### 8.3 Modelo Media

```bash
php artisan make:model Media
```

```php 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\MediaType;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'configuration',
        'semantic_context',
        'url_webhook',
        'is_active',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    // Relación Many-to-Many con Posts
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_medias')
                    ->withTimestamps();
    }

    // Relación Many-to-Many con Channels
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_medias')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, MediaType $type)
    {
        return $query->where('type', $type);
    }
}
```

### 8.4 Modelo Attachment

```bash
php artisan make:model Attachment
```

```php 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_name',
        'file_path',
        'mime_type',
        'size',
        'disk',
        'description',
        'download_count',
        'user_id',
    ];

    // Relación con User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor para obtener la URL de descarga
    public function getDownloadUrlAttribute(): string
    {
        return route('attachments.download', $this->id);
    }

    // Método para incrementar contador de descargas
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }
}
```

### 8.5 Extensión del Modelo User

```php 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relación HasMany
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    // Relación Many-to-Many con Channels
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'user_channels')
                    ->withPivot(['is_approved', 'approved_at'])
                    ->withTimestamps();
    }

    // Canales aprobados
    public function approvedChannels(): BelongsToMany
    {
        return $this->channels()->wherePivot('is_approved', true);
    }
}
```

## Paso 9: Ejecución de las Migraciones

Ejecuta todas las migraciones en orden:

```bash
php artisan migrate
```

Si necesitas revertir todas las migraciones y ejecutarlas de nuevo:

```bash
php artisan migrate:refresh
```

## Uso de las Tablas Pivot

### Ejemplo 1: Asignar un Post a un Channel

```php
// Crear relación
$post = Post::find(1);
$channel = Channel::find(1);

$post->channels()->attach($channel->id);

// O usando sync para múltiples canales
$post->channels()->sync([1, 2, 3]);
```

### Ejemplo 2: Asociar un User a un Channel con aprobación

```php
$user = User::find(1);
$channel = Channel::find(1);

// Solicitud inicial (sin aprobación)
$user->channels()->attach($channel->id, [
    'is_approved' => false,
    'approved_at' => null
]);

// Aprobar la solicitud
$user->channels()->updateExistingPivot($channel->id, [
    'is_approved' => true,
    'approved_at' => now()
]);
```

### Ejemplo 3: Asociar Media a un Post

```php
$post = Post::find(1);
$media = Media::find(1);

$post->medias()->attach($media->id);
```

### Ejemplo 4: Consultar relaciones con pivot

```php
// Obtener usuarios aprobados de un canal
$approvedUsers = $channel->users()->wherePivot('is_approved', true)->get();

// Obtener canales aprobados de un usuario
$user->approvedChannels()->get();

// Publicar un post programado
$post = Post::where('status', PostStatus::SCHEDULED)
           ->where('scheduled_at', '<=', now())
           ->first();

if ($post) {
    $post->update([
        'status' => PostStatus::PUBLISHED,
        'published_at' => now()
    ]);
}
```

## Comandos Útiles Adicionales

### Verificar estado de migraciones
```bash
php artisan migrate:status
```

### Crear seeders
```bash
php artisan make:seeder PostSeeder
```

### Crear factories
```bash
php artisan make:factory PostFactory
```

### Rollback de migraciones
```bash
php artisan migrate:rollback --step=1
```

## Conclusión

Este tutorial ha cubierto la creación completa de un sistema de migraciones y modelos para Laravel 12, incluyendo:

- Definición de enums para tipificar datos
- Migraciones para tablas principales
- Tablas pivot para relaciones Many-to-Many
- Modelos Eloquent con relaciones configuradas
- Ejemplos prácticos de uso

Las migraciones están diseñadas para ser escalables y mantener la integridad referencial de la base de datos. Los modelos incluyen todas las relaciones necesarias y métodos auxiliares para facilitar el desarrollo de la aplicación.
