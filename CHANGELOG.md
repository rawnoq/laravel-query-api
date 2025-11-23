# Changelog

All notable changes to `laravel-query-api` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-11-22

### Added
- Initial release
- Core QueryAPI service class with fluent API
- QueryAPIConfig base class for organizing query configurations
- Artisan command `make:query-api` for generating QueryAPI config classes
- Full HMVC support with rawnoq/laravel-hmvc package integration
- ResolvesModules trait for module path resolution
- Publishable stub templates for customization
- FilterBuilder class with support for all Spatie filter types:
  - Exact filters
  - Partial filters (LIKE)
  - Begins with filters
  - Ends with filters
  - Scope filters
  - Callback filters
  - Operator filters (EQUAL, NOT_EQUAL, GREATER_THAN, LESS_THAN, etc.)
  - Trashed filters (soft deletes)
  - Custom filters
- IncludeBuilder class with support for:
  - Relationship includes
  - Count includes
  - Exists includes
  - Callback includes
  - Custom includes
- FilterOperator enum for type-safe operator filters
- Support for sparse fieldsets (field selection)
- Support for sorting with multiple fields
- Support for default sorting
- Support for pagination
- Helper function `query_api()` for quick access
- QueryAPI Facade for static access
- Auto-discovery for Laravel package registration
- Comprehensive documentation
- MIT License

### Features
- Fluent API with method chaining
- Config class architecture for clean code organization
- Static method calls on config classes (e.g., `UserQueryAPI::get()`)
- Support for Eloquent Query Builder instances
- Whitelist-based security for fields, filters, and includes
- Compatible with Spatie Laravel Query Builder 6.x
- PHP 8.2+ type hints and features

## [Unreleased]

- Tests suite
- GitHub Actions CI/CD
- More examples and use cases

## [1.1.0] - 2024-12-XX

### Added
- **Virtual Fields Support** - Declare computed/accessor fields that aren't database columns
  - `virtualFields()` method in QueryAPIConfig to declare virtual fields
  - Automatic filtering of virtual fields from database queries
  - Validation of virtual fields against allowed fields
- **Helper Methods for Resources** - Built-in helpers for checking requested fields and includes
  - `getRequestedFields()` - Get all requested fields from request
  - `isFieldRequested()` - Check if a specific field is requested
  - `getRequestedIncludes()` - Get all requested includes from request
  - `isIncludeRequested()` - Check if a specific include is requested
- **Exclusion Filters** - New filter types for excluding values
  - `exclude()` - Exclude single value (WHERE field != value)
  - `excludeIn()` - Exclude multiple values (WHERE field NOT IN [...])
  - `whereNot()` - WHERE NOT with operator support
- **Flexible Pagination Methods** - New methods for dynamic pagination control
  - `getOrPaginate()` - Default get all, use `?paginate=1` for pagination
  - `paginateOrGet()` - Default paginate, use `?get=1` for all data
- **Pagination Configuration** - Configurable pagination limits per model
  - `defaultPerPage()` - Set default items per page (default: 20)
  - `maxPerPage()` - Set maximum items per page (default: 100)
  - `minPerPage()` - Set minimum items per page (default: 1)
- Enhanced stub template with virtual fields and pagination examples
- Comprehensive documentation for virtual fields, helper methods, and pagination

### Improved
- Better handling of field formats (fields[model], fields[_], etc.)
- Support for plural/singular model name variations in field requests
- More robust request field parsing
- **Performance Optimizations**
  - Model table name caching to avoid repeated model instantiation
  - Shared `getModelInfo()` method for DRY principle
  - Better error handling in helper methods
- **Code Quality**
  - Fixed `whereNot()` method to properly use operator parameter
  - Improved error handling with try-catch blocks
  - Better type safety and validation
