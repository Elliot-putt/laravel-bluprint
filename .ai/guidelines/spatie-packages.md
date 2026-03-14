# Spatie Package Integration

## Overview

This project uses multiple Spatie packages. Follow their conventions and best practices.

## Laravel Data

**For all Data Transfer Objects (DTOs).**

### Usage
```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class UserData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public Lazy|ProfileData $profile,
    ) {}
}
```

### Key Features
- Lazy-loaded relationships: `Lazy::create(fn () => ...)`
- Type transformations: `fromModel()`, `fromRequest()`, `toArray()`
- Validation: Define validation rules in Data classes
- TypeScript generation: Automatic type exports via `php artisan typescript:transform`

## Laravel Medialibrary

**For file uploads and media management.**

### Usage
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
```

### Adding Media
```php
$user->addMedia($request->file('avatar'))
    ->toMediaCollection('avatar');
```

### Retrieving Media
```php
$avatarUrl = $user->getFirstMediaUrl('avatar');
$allImages = $user->getMedia('images');
```

### Media Conversions
```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(150)
        ->height(150)
        ->sharpen(10);
}
```

## Laravel Model States

**For workflow state machines.**

### Usage
```php
use Spatie\ModelStates\HasStates;

class Order extends Model
{
    use HasStates;

    protected $casts = [
        'state' => OrderState::class,
    ];
}
```

### State Classes
```php
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class OrderState extends State
{
    abstract public function canShip(): bool;
}

class Pending extends OrderState
{
    public function canShip(): bool { return true; }
}

class Shipped extends OrderState
{
    public function canShip(): bool { return false; }
}
```

### Configuration
```php
public static function config(): StateConfig
{
    return StateConfig::create()
        ->default(Pending::class)
        ->allowTransition(Pending::class, Shipped::class);
}
```

### Important
- Type-safe state transitions
- Prevent invalid state changes
- Use state methods for business logic checks

## Laravel Permission

**For role-based access control.**

### Usage
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

### Checking Permissions
```php
// In controllers
if ($user->hasPermissionTo('edit posts')) {
    // ...
}

// In Blade
@can('edit posts')
    <!-- Edit button -->
@endcan
```

### Assigning Roles
```php
$user->assignRole('admin');
$user->givePermissionTo('edit posts');
```

## Laravel Sluggable

**For URL slug generation.**

### Usage
```php
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
}
```

## Laravel Health

**For application health monitoring.**

### Running Checks
```bash
php artisan health
```

### Custom Health Checks
```php
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class CustomCheck extends Check
{
    public function run(): Result
    {
        // Check logic
        return Result::make()->ok();
    }
}
```
