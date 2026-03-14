# Laravel Helpers and Collections First

**Always prefer Laravel's fluent helpers and Collection methods over native PHP when available.**

## Collections Over Native Arrays

Use `collect()` to transform arrays into powerful Collection objects with chainable methods.

### ✅ Good - Using Collections

```php
// Collection methods are fluent and readable
$active = collect($users)
    ->filter(fn ($user) => $user->isActive())
    ->map(fn ($user) => $user->name)
    ->values()
    ->all();

// Pluck for extracting values
$emails = collect($users)->pluck('email')->all();

// Group by with collections
$grouped = collect($posts)
    ->groupBy('category_id')
    ->map(fn ($group) => $group->count());

// Take/skip with collections
$recent = collect($items)->take(10);
$remaining = collect($items)->skip(10);
```

### ❌ Avoid - Native PHP Arrays

```php
// Harder to read and maintain
$active = array_values(
    array_map(
        fn ($user) => $user->name,
        array_filter($users, fn ($user) => $user->isActive())
    )
);

// Less expressive
$emails = array_column($users, 'email');

// More verbose
$grouped = [];
foreach ($posts as $post) {
    $grouped[$post->category_id][] = $post;
}
```

## Common Collection Methods

Use these methods instead of native PHP functions:

- `map()` instead of `array_map()`
- `filter()` instead of `array_filter()`
- `reduce()` instead of `array_reduce()`
- `pluck()` instead of `array_column()`
- `keys()` instead of `array_keys()`
- `values()` instead of `array_values()`
- `first()` instead of `reset()` or `array_first()`
- `last()` instead of `end()` or `array_last()`
- `contains()` instead of `in_array()`
- `isEmpty()` instead of `empty()`
- `isNotEmpty()` instead of `!empty()`
- `sum()` instead of `array_sum()`
- `avg()` instead of manual averaging
- `unique()` instead of `array_unique()`
- `sort()` / `sortBy()` instead of `sort()` / `usort()`
- `reverse()` instead of `array_reverse()`
- `chunk()` instead of `array_chunk()`
- `flatten()` instead of manual flattening
- `flip()` instead of `array_flip()`
- `merge()` instead of `array_merge()`
- `combine()` instead of `array_combine()`
- `each()` for iteration instead of `foreach` when chaining

## Laravel Helper Functions

### Array Helpers (`Illuminate\Support\Arr`)

```php
use Illuminate\Support\Arr;

// ✅ Good - Laravel helpers
$value = Arr::get($array, 'key.nested', 'default');
$value = data_get($object, 'nested.key', 'default'); // Also works with objects
$wrapped = Arr::wrap($value); // Ensures array
$dotted = Arr::dot($array); // Flatten to dot notation
$only = Arr::only($array, ['key1', 'key2']);
$except = Arr::except($array, ['password', 'token']);
$has = Arr::has($array, 'key.nested');
$first = Arr::first($array, fn ($value) => $value > 100);
$last = Arr::last($array, fn ($value) => $value > 100);
$collapsed = Arr::collapse($array); // Merge arrays within array

// ❌ Avoid - Native PHP or manual checks
$value = $array['key']['nested'] ?? 'default'; // Error-prone
$wrapped = is_array($value) ? $value : [$value];
```

### String Helpers (`Illuminate\Support\Str`)

```php
use Illuminate\Support\Str;

// ✅ Good - Laravel string helpers
$slug = Str::slug($title);
$contains = Str::contains($haystack, 'needle');
$startsWith = Str::startsWith($path, '/api');
$endsWith = Str::endsWith($filename, '.php');
$camel = Str::camel('foo_bar');
$snake = Str::snake('fooBar');
$studly = Str::studly('foo_bar');
$kebab = Str::kebab('fooBar');
$title = Str::title('hello world');
$limit = Str::limit($text, 100);
$random = Str::random(32);
$uuid = Str::uuid();
$before = Str::before($string, '@');
$after = Str::after($email, '@');
$between = Str::between($string, '[', ']');
$plural = Str::plural('child'); // children
$singular = Str::singular('children'); // child

// ❌ Avoid - Native PHP string functions
$slug = strtolower(str_replace(' ', '-', $title));
$contains = strpos($haystack, 'needle') !== false;
$startsWith = substr($path, 0, 4) === '/api';
```

### Value Helpers

```php
// ✅ Good - Laravel helpers
$result = value($callable); // Resolve value or callable
$result = with($object, fn ($obj) => $obj->method()); // Pass value to closure
$result = tap($object, fn ($obj) => $obj->save()); // Tap into value
$filled = filled($value); // Check if value is "filled" (not empty)
$blank = blank($value); // Check if value is "blank"
$optional = optional($user)->name; // Null-safe access
$transform = transform($value, fn ($v) => strtoupper($v)); // Transform if not null

// ❌ Avoid - Manual null checks
$name = $user !== null ? $user->name : null;
$result = !is_null($value) ? strtoupper($value) : null;
```

## Data Access Helpers

```php
// ✅ Good - data_get() for nested access
$city = data_get($user, 'address.city', 'Unknown');
$emails = data_get($users, '*.email'); // Get all emails with wildcard
$first = data_get($array, '0.name');

// Works with objects and arrays
$value = data_get($object, 'property.nested');

// ✅ Good - data_set() for nested updates
data_set($user, 'address.city', 'London');
data_set($users, '*.active', true); // Set all to active

// ✅ Good - data_fill() only sets if not exists
data_fill($user, 'role', 'guest');

// ❌ Avoid - Manual nested checks
$city = $user['address']['city'] ?? 'Unknown'; // Error if 'address' doesn't exist
```

## Eloquent Collections

Eloquent returns Collections by default - use their methods:

```php
// ✅ Good - Eloquent collection methods
$users = User::all();
$active = $users->where('active', true);
$names = $users->pluck('name');
$grouped = $users->groupBy('role');
$keyedById = $users->keyBy('id');
$contains = $users->contains('email', 'test@example.com');
$find = $users->find(1); // Find by primary key in collection
$loadMissing = $users->loadMissing('posts'); // Lazy eager load

// ❌ Avoid - Converting to array unnecessarily
$users = User::all()->toArray();
$active = array_filter($users, fn ($u) => $u['active']);
```

## When to Use Native PHP

Use native PHP when:

1. **Performance is critical** and you're processing large datasets (millions of rows)
2. **Simple operations** where Collections add no value:
   ```php
   // OK to use native PHP for simple cases
   count($array)
   empty($array)
   isset($array[$key])
   ```
3. **Working with primitives** where Collections don't help:
   ```php
   // OK - simple string operations
   strlen($string)
   trim($string)
   ```

## Higher-Order Collection Messages

Use higher-order messages for cleaner code:

```php
// ✅ Good - Higher-order messages
$users->each->save();
$users->each->notify(new Welcome);
$users->map->name;
$users->filter->isActive()->values();

// ❌ Verbose - Explicit closures
$users->each(fn ($user) => $user->save());
$users->map(fn ($user) => $user->name);
```

## Collection Pipelines

Chain Collection methods for readable data transformations:

```php
// ✅ Good - Collection pipeline
$report = collect($orders)
    ->filter(fn ($order) => $order->isPaid())
    ->groupBy('customer_id')
    ->map(fn ($orders) => [
        'total' => $orders->sum('amount'),
        'count' => $orders->count(),
        'average' => $orders->avg('amount'),
    ])
    ->sortByDesc('total')
    ->take(10)
    ->values();
```

## Lazy Collections

For large datasets, use Lazy Collections to avoid memory issues:

```php
use Illuminate\Support\LazyCollection;

LazyCollection::make(function () {
    $file = fopen('large-file.csv', 'r');
    while (($line = fgetcsv($file)) !== false) {
        yield $line;
    }
    fclose($file);
})
->filter(fn ($row) => $row[2] === 'active')
->map(fn ($row) => ['name' => $row[0], 'email' => $row[1]])
->chunk(100)
->each(fn ($chunk) => User::insert($chunk->toArray()));
```

## Quick Reference

### Array Operations
- ✅ `collect($array)->map()` instead of ❌ `array_map()`
- ✅ `collect($array)->filter()` instead of ❌ `array_filter()`
- ✅ `Arr::get()` or `data_get()` instead of ❌ `$array['key']['nested'] ?? null`
- ✅ `Arr::wrap()` instead of ❌ `is_array($value) ? $value : [$value]`
- ✅ `collect($array)->pluck('key')` instead of ❌ `array_column()`

### String Operations
- ✅ `Str::contains()` instead of ❌ `strpos() !== false`
- ✅ `Str::startsWith()` instead of ❌ `substr($str, 0, $len) === $prefix`
- ✅ `Str::slug()` instead of ❌ manual slug generation
- ✅ `Str::random()` instead of ❌ manual random string generation

### Null Safety
- ✅ `optional($user)->name` instead of ❌ `$user ? $user->name : null`
- ✅ `data_get($array, 'key', 'default')` instead of ❌ `$array['key'] ?? 'default'`
- ✅ `blank($value)` instead of ❌ `is_null($value) || $value === ''`
