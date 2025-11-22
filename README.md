# Laravel Query API

[![Latest Version](https://img.shields.io/packagist/v/rawnoq/laravel-query-api.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-query-api)
[![Total Downloads](https://img.shields.io/packagist/dt/rawnoq/laravel-query-api.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-query-api)
[![License](https://img.shields.io/packagist/l/rawnoq/laravel-query-api.svg?style=flat-square)](https://packagist.org/packages/rawnoq/laravel-query-api)

A powerful and elegant Laravel package that brings GraphQL-like flexibility to your REST APIs. Built on top of [Spatie Laravel Query Builder](https://github.com/spatie/laravel-query-builder), it provides a fluent API for managing complex queries with ease.

## Features

- ✅ **Sorting** - Sort results by multiple fields with ascending/descending order
- ✅ **Default Sorting** - Define default sorting behavior
- ✅ **Sparse Fieldsets** - Request only the fields you need
- ✅ **Filters** - Support for exact, partial, scope, callback, operator, and custom filters
- ✅ **Includes (Relationships)** - Load relationships with count, exists, and custom includes
- ✅ **Pagination** - Built-in pagination support
- ✅ **Config Classes** - Organize query configurations in dedicated classes
- ✅ **Fluent API** - Beautiful and intuitive API with method chaining
- ✅ **HMVC Support** - Full compatibility with rawnoq/laravel-hmvc package
- ✅ **Artisan Command** - Generate QueryAPI config classes easily
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
->paginate(20)
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

## Security

The package implements a whitelist-based security model:

1. **Fields** - Only explicitly allowed fields can be selected
2. **Filters** - Only explicitly allowed filters can be applied
3. **Includes** - Only explicitly allowed relationships can be loaded
4. **Sorts** - Only explicitly allowed fields can be sorted

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
