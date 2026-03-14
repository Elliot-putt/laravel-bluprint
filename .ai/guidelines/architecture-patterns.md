# Laravel Architecture Patterns

## Action Classes

**Use Actions for complex business logic operations.**

### Location & Structure
- Located in `app/Actions/{Domain}/`
- Single `handle()` method with clear return type
- Use dependency injection for services
- Mark classes `readonly` when possible

### Example

```php
declare(strict_types=1);

namespace App\Actions\Websites;

use App\Data\WebsiteData;
use App\Models\Website;

readonly class CreateWebsite
{
    public function __construct(
        private SpectreIdService $spectreIdService,
    ) {}

    public function handle(CreateWebsiteRequest $request): WebsiteData
    {
        // Business logic here
        $website = Website::create([...]);

        return WebsiteData::from($website);
    }
}
```

### When to Create Actions
- Complex multi-step operations
- Business logic that shouldn't live in controllers
- Operations that might be reused across controllers/commands/jobs

## Services

**Use Services for domain-specific external integrations and complex queries.**

### Service Patterns
- Clear return types
- Use performance-optimized queries (`updateOrCreate`, `upsert`)
- Implement contracts for testing with fakes
- Never call other services directly - inject them
- Use repository pattern for data access

## Data Transfer Objects

**Use Spatie Laravel Data for all API responses and data transformation.**

### Location & Structure
- Located in `app/Data/` with domain subdirectories
- Extend `\Spatie\LaravelData\Data`
- Include `fromModel()` and `fromRequest()` static methods
- Support lazy-loaded relations for performance

### Example

```php
declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class WebsiteData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $url,
        public Lazy|CompanyData $company,
    ) {}

    public static function fromModel(Website $website): self
    {
        return new self(
            id: $website->id,
            name: $website->name,
            url: $website->url,
            company: Lazy::create(fn () => CompanyData::from($website->company)),
        );
    }
}
```

### TypeScript Generation
- Run `php artisan typescript:transform` to generate TypeScript types
- Types available as `App.Data.WebsiteData` in Vue components

## Form Requests

**Always create dedicated FormRequest classes for validation.**

### Pattern
```php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWebsiteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a website name.',
        ];
    }
}
```

### Important
- Never use inline validation in controllers
- Type-hint FormRequest in controller methods
- Don't use enum database types - use string/varchar with validation rules

## Controllers

**Keep controllers thin - delegate to Actions and Services.**

### Structure
- Extend `Controller` base class
- Use constructor dependency injection
- Return `Inertia::render()` or `RedirectResponse`
- For CRUD: use standard methods (index, create, store, show, edit, update, destroy)
- For custom actions: create separate invokable controllers

### Example

```php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Websites\CreateWebsite;
use App\Http\Requests\CreateWebsiteRequest;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function store(
        CreateWebsiteRequest $request,
        CreateWebsite $action,
    ): RedirectResponse {
        $website = $action->handle($request);

        return redirect()
            ->route('websites.show', ['website' => $website->id])
            ->with('success', 'Website created successfully');
    }
}
```

## Route Naming

**Always use named routes, never hardcode URLs.**

### Backend
```php
// ✅ Good - Use route names
return redirect(route('websites.show', ['website' => $website->id]));

// ❌ Bad - Hardcoded URL
return redirect("/websites/{$website->id}");
```

### Frontend
```vue
<!-- ✅ Good - Use Ziggy route helper -->
<script setup lang="ts">
const navigateToWebsite = (websiteId: string) => {
  router.visit(route('websites.show', { website: websiteId }))
}
</script>

<!-- ❌ Bad - Hardcoded path -->
<script setup lang="ts">
const navigateToWebsite = (websiteId: string) => {
  router.visit(`/websites/${websiteId}`)
}
</script>
```

## Database Conventions

- **Always use UUID** for primary and foreign keys
- Use `foreignUuid()` for relationships
- Include cascading deletes: `onDelete('cascade')`
- Table names: plural form
- No enum column types - use string/varchar
