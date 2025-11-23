# Laravel Query API

[![Latest Version](https://img.shields.io/packagist/v/rawnoq/laravel-query-api.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-query-api)
[![Total Downloads](https://img.shields.io/packagist/dt/rawnoq/laravel-query-api.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-query-api)
[![License](https://img.shields.io/packagist/l/rawnoq/laravel-query-api.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-query-api)

A powerful and elegant Laravel package that brings GraphQL-like flexibility to your REST APIs. Built on top of [Spatie Laravel Query Builder](https://github.com/spatie/laravel-query-builder), it provides a fluent API for managing complex queries with ease.

## Features

- ✅ **Sorting** - Sort results by multiple fields with ascending/descending order
- ✅ **Default Sorting** - Define default sorting behavior
- ✅ **Sparse Fieldsets** - Request only the fields you need
- ✅ **Virtual Fields** - Support for computed/accessor fields that aren't database columns
- ✅ **Filters** - Support for exact, partial, scope, callback, operator, exclusion, and custom filters
- ✅ **Includes (Relationships)** - Load relationships with count, exists, and custom includes
- ✅ **Pagination** - Built-in pagination support
- ✅ **Config Classes** - Organize query configurations in dedicated classes
- ✅ **Fluent API** - Beautiful and intuitive API with method chaining
- ✅ **HMVC Support** - Full compatibility with rawnoq/laravel-hmvc package
- ✅ **Artisan Command** - Generate QueryAPI config classes easily
- ✅ **Helper Methods** - Built-in helpers for checking requested fields and includes in Resources
- ✅ **Security** - Whitelist-based access control for fields, filters, and relationships

## Requirements

- PHP >= 8.2
- Laravel >= 12.0
- Spatie Laravel Query Builder >= 6.0

## Installation

Install the package via Composer:

```bash
composer require rawnoq/laravel-query-api
```

The package will automatically register itself via Laravel's package discovery.

## Quick Start

### Generate QueryAPI Config Class

The easiest way to get started is to generate a QueryAPI config class using the Artisan command:

```bash
# Generate a QueryAPI config for a model
php artisan make:query-api UserQueryAPI --model=User

# Or let it guess the model from the class name
php artisan make:query-api UserQueryAPI

# For HMVC modules (requires rawnoq/laravel-hmvc)
php artisan make:query-api SettingQueryAPI --model=Setting --module=Settings
```

This will create a file at `app/QueryAPI/UserQueryAPI.php` (or `modules/{Module}/App/QueryAPI/` for HMVC) with all the basic methods stubbed out.

### Basic Usage with Helper Function

```php
use App\Models\User;

// Simple query
$users = query_api(User::class)->get();

// With sorting and pagination
$users = query_api(User::class)
    ->sort(['name', 'created_at'])
    ->paginate(20);
```

### Using Config Classes (Recommended)

Create a config class to organize your query settings:

```php
namespace App\QueryAPI;

use Rawnoq\QueryAPI\QueryAPIConfig;

class UserQueryAPI extends QueryAPIConfig
{
    public static function fields(): array
    {
        return ['id', 'name', 'email', 'created_at'];
    }

    public static function sorts(): array
    {
        return ['id', 'name', 'created_at'];
    }

    public static function defaultSort(): string
    {
        return '-created_at'; // Descending by created_at
    }

    public static function filters(): array
    {
        return [
            self::filter()->exact('id'),
            self::filter()->partial('name'),
            self::filter()->partial('email'),
        ];
    }

    public static function includes(): array
    {
        return [
            self::include()->relationship('posts'),
            self::include()->count('postsCount', 'posts'),
        ];
    }
}
```

Use the config class in your controller:

```php
use App\QueryAPI\UserQueryAPI;

// Direct static call
$users = UserQueryAPI::get();
$users = UserQueryAPI::paginate(20);

// Flexible pagination methods
$users = UserQueryAPI::getOrPaginate();      // Default: get all, use ?paginate=1 for pagination
$users = UserQueryAPI::paginateOrGet();      // Default: paginate, use ?get=1 for all

// With custom query
$users = UserQueryAPI::for(User::where('is_active', true))->get();
```

## Query Parameters

### Sorting

```http
GET /api/users?sort=name                    # Ascending
GET /api/users?sort=-created_at             # Descending
GET /api/users?sort=name,-created_at        # Multiple
```

### Fields (Sparse Fieldsets)

```http
GET /api/users?fields[users]=id,name,email
GET /api/users?fields[users]=id,name&fields[posts]=id,title
```

### Filters

**Partial Filter (LIKE):**
```http
GET /api/users?filter[name]=john
```

**Exact Filter:**
```http
GET /api/users?filter[id]=1
GET /api/users?filter[status]=active
```

**Operator Filters:**
```http
GET /api/users?filter[age]=>25              # Greater than
GET /api/users?filter[price]=<100           # Less than
GET /api/users?filter[score]=>50            # Dynamic operator
```

**Scope Filters:**
```http
GET /api/users?filter[active]=1
```

**Exclusion Filters:**
```http
GET /api/users?filter[exclude_status]=deleted        # WHERE status != 'deleted'
GET /api/users?filter[exclude_id]=1,2,3              # WHERE id NOT IN (1,2,3)
```

### Includes (Relationships)

```http
GET /api/users?include=posts                # Single relationship
GET /api/users?include=posts,roles          # Multiple relationships
GET /api/users?include=posts.comments       # Nested relationships
GET /api/users?include=postsCount           # Relationship count
GET /api/users?include=postsExists          # Relationship exists
```

### Pagination

```http
GET /api/users?page=2&per_page=15
```

**Flexible Pagination Methods:**

```http
# getOrPaginate() - Default: get all, use ?paginate=1 for pagination
GET /api/users                    # Returns all users (Collection)
GET /api/users?paginate=1        # Returns paginated (LengthAwarePaginator)
GET /api/users?paginate=1&per_page=50  # Returns paginated with custom per_page

# paginateOrGet() - Default: paginate, use ?get=1 for all
GET /api/users                    # Returns paginated (LengthAwarePaginator)
GET /api/users?get=1              # Returns all users (Collection)
GET /api/users?per_page=50        # Returns paginated with custom per_page
```

### Complete Example

```http
GET /api/users?include=posts&fields[users]=id,name,email&fields[posts]=id,title&filter[name]=john&filter[status]=active&sort=-created_at&page=1&per_page=20
```

## Available Methods

### Core Methods

```php
// Set target model or query
->for(User::class)
->for(User::where('active', true))

// Configure allowed fields
->fields(['id', 'name', 'email'])

// Configure allowed sorting
->sort(['id', 'name', 'created_at'])
->defaultSort('-created_at')

// Configure allowed filters
->filters(['name', 'email'])

// Configure allowed includes
->includes(['posts', 'roles'])

// Set config class
->config(UserQueryAPI::class)

// Execute query
->get()
->paginate(20)  // or paginate() to use defaultPerPage() from config

// Flexible pagination methods
->getOrPaginate()    // Default: get all, use ?paginate=1 for pagination
->paginateOrGet()    // Default: paginate, use ?get=1 for all
```

## Filter Types

### Exact Filter

```php
self::filter()->exact('id')
self::filter()->exact('status')
```

### Partial Filter (LIKE %value%)

```php
self::filter()->partial('name')
self::filter()->partial('email')
```

### Begins With Filter (LIKE value%)

```php
self::filter()->beginsWith('email')
```

### Ends With Filter (LIKE %value)

```php
self::filter()->endsWith('domain')
```

### Scope Filter

```php
self::filter()->scope('active')
self::filter()->scope('published')
```

### Callback Filter

```php
self::filter()->callback('has_posts', function ($query) {
    $query->whereHas('posts');
})
```

### Operator Filter

```php
use Rawnoq\QueryAPI\Enums\FilterOperator;

self::filter()->operator('age', FilterOperator::GREATER_THAN)
self::filter()->operator('price', FilterOperator::LESS_THAN)
self::filter()->operator('salary', FilterOperator::DYNAMIC) // Allows: >3000, <100, etc.
```

### Trashed Filter (Soft Deletes)

```php
self::filter()->trashed()
```

### Exclusion Filters

```php
// Exclude single value (WHERE field != value)
self::filter()->exclude('status', 'internal_status')

// Exclude multiple values (WHERE field NOT IN [...])
self::filter()->excludeIn('id', 'internal_id')

// WHERE NOT with operator
self::filter()->whereNot('status', '!=', 'internal_status')
```

## Include Types

### Relationship Include

```php
self::include()->relationship('posts')
self::include()->relationship('profile', 'userProfile') // With alias
```

### Count Include

```php
self::include()->count('postsCount', 'posts')
```

### Exists Include

```php
self::include()->exists('postsExists', 'posts')
```

### Callback Include

```php
self::include()->callback('latest_post', function ($query) {
    $query->latestOfMany();
})
```

### Custom Include

```php
self::include()->custom('comments_sum_votes', new AggregateInclude('votes', 'sum'), 'comments')
```

## Pagination Configuration

You can configure pagination defaults and limits per model:

```php
class UserQueryAPI extends QueryAPIConfig
{
    /**
     * Default items per page
     */
    public static function defaultPerPage(): int
    {
        return 15; // Default: 20
    }

    /**
     * Maximum items per page
     */
    public static function maxPerPage(): int
    {
        return 100; // Default: 100
    }

    /**
     * Minimum items per page
     */
    public static function minPerPage(): int
    {
        return 1; // Default: 1
    }
}
```

**Usage:**
```php
// Uses defaultPerPage() if per_page not in request
$users = UserQueryAPI::paginate();

// Reads per_page from request, applies min/max limits
$users = UserQueryAPI::paginate(); // Request: ?per_page=50

// Flexible methods
$users = UserQueryAPI::getOrPaginate();  // ?paginate=1&per_page=50
$users = UserQueryAPI::paginateOrGet();  // ?per_page=50 or ?get=1
```

## Advanced Usage

### Using with Eloquent Query

```php
$query = User::where('is_active', true)
    ->where('role', 'admin');

$users = UserQueryAPI::for($query)->get();
```

### Manual Configuration

```php
$users = query_api(User::class)
    ->fields(['id', 'name', 'email'])
    ->filters([
        filter_exact('id'),
        filter_partial('name'),
    ])
    ->includes([
        'posts',
        'roles',
    ])
    ->sort(['name', 'created_at'])
    ->defaultSort('-created_at')
    ->paginate(20);
```

### Using Facade

```php
use Rawnoq\QueryAPI\Facades\QueryAPI;

$users = QueryAPI::for(User::class)
    ->fields(['id', 'name'])
    ->sort(['name'])
    ->get();
```

## Virtual Fields

Virtual fields are computed fields or accessors that aren't actual database columns. They can be requested in API calls but won't cause SQL errors.

### Defining Virtual Fields

```php
class UserQueryAPI extends QueryAPIConfig
{
    public static function fields(): array
    {
        return ['id', 'name', 'email', 'full_name']; // full_name is virtual
    }

    public static function virtualFields(): array
    {
        return ['full_name']; // Declare as virtual
    }
}
```

### Using in Resources

```php
use App\QueryAPI\UserQueryAPI;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->when(
                UserQueryAPI::isFieldRequested('id'),
                $this->id
            ),
            'name' => $this->when(
                UserQueryAPI::isFieldRequested('name'),
                $this->name
            ),
            'email' => $this->when(
                UserQueryAPI::isFieldRequested('email'),
                $this->email
            ),
            'full_name' => $this->when(
                UserQueryAPI::isFieldRequested('full_name'),
                $this->first_name . ' ' . $this->last_name
            ),
            'posts' => $this->when(
                UserQueryAPI::isIncludeRequested('posts'),
                fn () => PostResource::collection($this->posts)
            ),
        ];
    }
}
```

### Helper Methods

The package provides helper methods for checking requested fields and includes:

```php
// Check if a field is requested
UserQueryAPI::isFieldRequested('email', $request);

// Get all requested fields
$fields = UserQueryAPI::getRequestedFields($request);

// Check if an include is requested
UserQueryAPI::isIncludeRequested('posts', $request);

// Get all requested includes
$includes = UserQueryAPI::getRequestedIncludes($request);
```

## Performance

The package includes several performance optimizations:

- **Model Table Caching**: Table names are cached to avoid repeated model instantiation
- **Efficient Field Parsing**: Optimized parsing of field formats
- **Lazy Loading**: Relationships are only loaded when explicitly requested

To clear the model table cache (useful for testing):

```php
use Rawnoq\QueryAPI\QueryAPI;

QueryAPI::clearModelTableCache();
```

## Security

The package implements a whitelist-based security model:

1. **Fields** - Only explicitly allowed fields can be selected
2. **Virtual Fields** - Computed fields that are validated but not queried from database
3. **Filters** - Only explicitly allowed filters can be applied
4. **Includes** - Only explicitly allowed relationships can be loaded
5. **Sorts** - Only explicitly allowed fields can be sorted

Any unauthorized request will be silently ignored or throw an exception based on Spatie Query Builder configuration.

## Artisan Commands

### make:query-api

Generate a new QueryAPI configuration class:

```bash
php artisan make:query-api {name} --model={ModelName}
```

**Arguments:**
- `name` - The name of the QueryAPI config class (e.g., UserQueryAPI)

**Options:**
- `--model, -m` - The model that this QueryAPI config is for
- `--module` - The module that this QueryAPI config belongs to (for HMVC structure)
- `--force, -f` - Create the class even if it already exists

**Examples:**

```bash
# Generate with explicit model
php artisan make:query-api UserQueryAPI --model=User

# Generate and let it auto-detect the model
php artisan make:query-api PostQueryAPI

# Generate for a specific module (HMVC)
php artisan make:query-api SettingQueryAPI --model=Setting --module=Settings

# Force overwrite existing file
php artisan make:query-api UserQueryAPI --model=User --force
```

### Publishing Stubs

You can publish the command stub for customization:

```bash
php artisan vendor:publish --tag=query-api-stubs
```

This will copy the stub file to `stubs/query-api.stub` in your project root where you can customize it.

## Advanced Examples

### Edge Cases

**Handling Empty Results:**
```php
$users = UserQueryAPI::for(User::where('deleted', true))->get();
if ($users->isEmpty()) {
    return response()->json(['message' => 'No users found'], 404);
}
```

**Custom Query with Filters:**
```php
$activeUsers = UserQueryAPI::for(
    User::where('status', 'active')
        ->where('verified', true)
)->paginate(10);
```

**Multiple Field Formats:**
```http
# All these formats work:
GET /api/users?fields=id,name
GET /api/users?fields[users]=id,name
GET /api/users?fields[_]=id,name
```

**Virtual Fields with Nested Resources:**
```php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->when(
                UserQueryAPI::isFieldRequested('id'),
                $this->id
            ),
            'full_name' => $this->when(
                UserQueryAPI::isFieldRequested('full_name'),
                "{$this->first_name} {$this->last_name}"
            ),
            'posts' => $this->when(
                UserQueryAPI::isIncludeRequested('posts'),
                fn() => PostResource::collection($this->posts)
            ),
        ];
    }
}
```

**Complex Filtering:**
```php
// In your QueryAPIConfig
public static function filters(): array
{
    return [
        // Multiple filters on same field
        self::filter()->exact('status'),
        self::filter()->partial('name'),
        
        // Exclusion filters
        self::filter()->exclude('exclude_status', 'status'),
        self::filter()->excludeIn('exclude_ids', 'id'),
        
        // Operator filters
        self::filter()->operator('age', FilterOperator::GREATER_THAN),
        self::filter()->operator('price', FilterOperator::DYNAMIC), // Allows >, <, >=, <=
        
        // Callback with complex logic
        self::filter()->callback('has_recent_posts', function ($query, $value) {
            if ($value) {
                $query->whereHas('posts', function ($q) {
                    $q->where('created_at', '>', now()->subDays(7));
                });
            }
        }),
    ];
}
```

## Troubleshooting

### Common Issues

**Issue: "Requested field(s) are not allowed"**
```php
// Make sure the field is in your fields() method
public static function fields(): array
{
    return ['id', 'name', 'email']; // Add missing field here
}
```

**Issue: Virtual field causing SQL errors**
```php
// Make sure to declare it in virtualFields()
public static function virtualFields(): array
{
    return ['full_name', 'value']; // Add virtual field here
}
```

**Issue: Includes not loading**
```php
// Check if the include is allowed
public static function includes(): array
{
    return [
        self::include()->relationship('posts'), // Make sure it's here
    ];
}

// And check if it's requested in the Resource
'posts' => $this->when(
    UserQueryAPI::isIncludeRequested('posts'),
    fn() => $this->posts
)
```

**Issue: Model class not found**
```php
// Make sure model() method returns correct class
public static function model(): string
{
    return User::class; // Use full namespace if needed: \App\Models\User::class
}
```

**Issue: Performance with large datasets**
```php
// Use pagination and limit fields
$users = UserQueryAPI::paginate(20); // Instead of get()

// Request only needed fields
GET /api/users?fields[users]=id,name&per_page=20
```

**Issue: Filter not working**
```php
// Check filter type matches your use case
// For exact match:
self::filter()->exact('status') // ?filter[status]=active

// For partial match:
self::filter()->partial('name') // ?filter[name]=john (matches "john", "johnny", etc.)

// For exclusion:
self::filter()->exclude('exclude_status', 'status') // ?filter[exclude_status]=deleted
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on recent changes.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security Vulnerabilities

If you discover any security-related issues, please email info@rawnoq.com instead of using the issue tracker.

## Credits

- [Rawnoq](https://github.com/rawnoq)
- [Spatie](https://spatie.be) for the amazing [Laravel Query Builder](https://github.com/spatie/laravel-query-builder)
- All [contributors](https://github.com/rawnoq/laravel-query-api/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
