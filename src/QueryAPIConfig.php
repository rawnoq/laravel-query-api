<?php

namespace Rawnoq\QueryAPI;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Rawnoq\QueryAPI\FilterBuilder;
use Rawnoq\QueryAPI\IncludeBuilder;

/**
 * QueryAPIConfig Base Class
 * 
 * Base class for QueryAPI configuration classes
 * Extend this class to define QueryAPI settings for your models
 * 
 * @package Rawnoq\QueryAPI
 */
abstract class QueryAPIConfig
{
    /**
     * Get FilterBuilder instance
     *
     * @return FilterBuilder
     */
    public static function filter(): FilterBuilder
    {
        return new FilterBuilder();
    }

    /**
     * Get IncludeBuilder instance
     *
     * @return IncludeBuilder
     */
    public static function include(): IncludeBuilder
    {
        return new IncludeBuilder();
    }
    /**
     * Get the model class associated with this config
     * Must be overridden in child classes
     *
     * @return string
     */
    public static function model(): string
    {
        throw new \BadMethodCallException('The model() method must be implemented in ' . static::class);
    }

    /**
     * Get allowed fields for selection
     *
     * @return array|string|null
     */
    public static function fields(): array|string|null
    {
        return null;
    }

    /**
     * Get allowed filters
     *
     * @return array|string|null
     */
    public static function filters(): array|string|null
    {
        return null;
    }

    /**
     * Get allowed includes (relationships)
     *
     * @return array|string|null
     */
    public static function includes(): array|string|null
    {
        return null;
    }

    /**
     * Get allowed sortable fields
     *
     * @return array|string|null
     */
    public static function sorts(): array|string|null
    {
        return null;
    }

    /**
     * Get default sort
     *
     * @return array|string|null
     */
    public static function defaultSort(): array|string|null
    {
        return null;
    }

    /**
     * Get virtual fields (fields that are not database columns)
     * 
     * Virtual fields are fields that are computed or accessed via accessors
     * but are not actual database columns. These fields should be validated
     * against the fields() array but should not be passed to Spatie QueryBuilder
     * as it will reject them.
     * 
     * @example
     * // In your QueryAPIConfig:
     * public static function virtualFields(): array
     * {
     *     return ['value', 'full_name'];
     * }
     * 
     * // In your Resource:
     * 'value' => $this->when(
     *     UserQueryAPI::isFieldRequested('value'),
     *     $this->resolved_value
     * )
     *
     * @return array
     */
    public static function virtualFields(): array
    {
        return [];
    }

    /**
     * Get default per page value for pagination
     * 
     * @return int
     */
    public static function defaultPerPage(): int
    {
        return 20;
    }

    /**
     * Get maximum per page value for pagination
     * 
     * @return int
     */
    public static function maxPerPage(): int
    {
        return 100;
    }

    /**
     * Get minimum per page value for pagination
     * 
     * @return int
     */
    public static function minPerPage(): int
    {
        return 1;
    }

    /**
     * Set a different model or Eloquent Builder for this query
     *
     * @param string|EloquentBuilder $model
     * @return QueryAPI
     */
    public static function for(string|EloquentBuilder $model): QueryAPI
    {
        return query_api($model, static::class);
    }

    /**
     * Get all data using this config
     *
     * @return Collection
     */
    public static function get(): Collection
    {
        return query_api(static::model(), static::class)->get();
    }

    /**
     * Get data with pagination using this config
     *
     * @param int|null $perPage If null, reads from request or uses defaultPerPage() from config
     * @return LengthAwarePaginator
     */
    public static function paginate(?int $perPage = null): LengthAwarePaginator
    {
        // If perPage is not provided, read from request first, then use default
        if ($perPage === null) {
            $request = request();
            $perPage = $request->input('per_page');
            if ($perPage === null) {
                $perPage = static::defaultPerPage();
            }
        }
        
        return query_api(static::model(), static::class)->paginate($perPage);
    }

    /**
     * Get all data, but allow pagination via request parameter
     * 
     * Default behavior is to return all data (get()).
     * If 'paginate' parameter exists in request, uses pagination instead.
     * 
     * @example
     * GET /api/settings                    // Returns all (Collection)
     * GET /api/settings?paginate=1        // Returns paginated (LengthAwarePaginator)
     * GET /api/settings?paginate=1&per_page=50  // Returns paginated with custom per_page
     * 
     * @return Collection|LengthAwarePaginator
     */
    public static function getOrPaginate(): Collection|LengthAwarePaginator
    {
        $request = request();
        
        // Check if paginate parameter exists
        if ($request->has('paginate') && $request->boolean('paginate')) {
            return static::paginate();
        }
        
        return static::get();
    }

    /**
     * Get paginated data, but allow get all via request parameter
     * 
     * Default behavior is to return paginated data.
     * If 'get' parameter exists in request, returns all data instead.
     * 
     * @example
     * GET /api/settings                    // Returns paginated (LengthAwarePaginator)
     * GET /api/settings?get=1              // Returns all (Collection)
     * GET /api/settings?per_page=50        // Returns paginated with custom per_page
     * 
     * @return Collection|LengthAwarePaginator
     */
    public static function paginateOrGet(): Collection|LengthAwarePaginator
    {
        $request = request();
        
        // Check if get parameter exists
        if ($request->has('get') && $request->boolean('get')) {
            return static::get();
        }
        
        return static::paginate();
    }

    /**
     * Get requested fields from request for this model
     * 
     * This helper method extracts the requested fields from the request
     * query parameters, handling different formats (fields[model], fields[_], etc.)
     *
     * @param \Illuminate\Http\Request|null $request
     * @return array Array of requested field names
     * @throws \BadMethodCallException If model() method is not implemented
     */
    public static function getRequestedFields(?\Illuminate\Http\Request $request = null): array
    {
        $request = $request ?? request();
        $requestedFields = $request->input('fields', []);
        
        // Get model name and table name with error handling
        try {
            $modelClass = static::model();
            
            if (!class_exists($modelClass)) {
                return [];
            }
            
            $modelName = strtolower(class_basename($modelClass));
            $modelTableName = 'model';
            
            if (method_exists($modelClass, 'getTable')) {
                try {
                    $modelInstance = new $modelClass();
                    $modelTableName = $modelInstance->getTable();
                } catch (\Throwable $e) {
                    // If model can't be instantiated, use default
                    $modelTableName = $modelName;
                }
            }
        } catch (\BadMethodCallException $e) {
            // If model() method is not implemented, return empty array
            return [];
        } catch (\Throwable $e) {
            // For any other error, return empty array
            return [];
        }
        
        $modelNamePlural = \Illuminate\Support\Str::plural($modelName);
        $modelNameSingular = \Illuminate\Support\Str::singular($modelName);
        
        $modelFields = [];
        
        if (is_string($requestedFields)) {
            // Format: fields=field1,field2
            $modelFields = explode(',', $requestedFields);
        } elseif (is_array($requestedFields)) {
            // Check all possible keys: table name, model name (singular/plural), and underscore
            $keysToCheck = [
                $modelTableName,    // e.g., 'settings'
                $modelName,         // e.g., 'setting'
                $modelNamePlural,   // e.g., 'settings'
                $modelNameSingular, // e.g., 'setting'
                '_',                // default format
            ];
            
            foreach ($keysToCheck as $key) {
                if (isset($requestedFields[$key])) {
                    $modelFields = is_string($requestedFields[$key]) 
                        ? explode(',', $requestedFields[$key]) 
                        : $requestedFields[$key];
                    break; // Found the fields, stop checking
                }
            }
        }
        
        return array_map('trim', $modelFields);
    }

    /**
     * Check if a specific field is requested in the request
     *
     * @param string $fieldName
     * @param \Illuminate\Http\Request|null $request
     * @return bool
     */
    public static function isFieldRequested(string $fieldName, ?\Illuminate\Http\Request $request = null): bool
    {
        $requestedFields = static::getRequestedFields($request);
        return in_array($fieldName, $requestedFields) || empty($requestedFields);
    }

    /**
     * Get requested includes (relationships) from request
     * 
     * This helper method extracts the requested includes from the request
     * query parameters, handling different formats (include=relation1,relation2)
     *
     * @param \Illuminate\Http\Request|null $request
     * @return array Array of requested include names
     */
    public static function getRequestedIncludes(?\Illuminate\Http\Request $request = null): array
    {
        try {
            $request = $request ?? request();
            
            if (!$request) {
                return [];
            }
            
            $requestedIncludes = $request->input('include', '');
            
            if (empty($requestedIncludes)) {
                return [];
            }
            
            if (is_string($requestedIncludes)) {
                return array_map('trim', explode(',', $requestedIncludes));
            }
            
            if (is_array($requestedIncludes)) {
                return array_map('trim', $requestedIncludes);
            }
            
            return [];
        } catch (\Throwable $e) {
            // If any error occurs, return empty array
            return [];
        }
    }

    /**
     * Check if a specific include (relationship) is requested in the request
     *
     * @param string $includeName
     * @param \Illuminate\Http\Request|null $request
     * @return bool
     */
    public static function isIncludeRequested(string $includeName, ?\Illuminate\Http\Request $request = null): bool
    {
        $requestedIncludes = static::getRequestedIncludes($request);
        return in_array($includeName, $requestedIncludes);
    }
}

