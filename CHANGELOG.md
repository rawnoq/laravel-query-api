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
