<?php

namespace Rawnoq\QueryAPI;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Rawnoq\QueryAPI\QueryAPIConfig;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * QueryAPI Service
 * 
 * Advanced API query management system for sorting
 * Built on REST API with support for sorting
 * 
 * @package Rawnoq\QueryAPI
 */
class QueryAPI
{
    /**
     * Target model class or Eloquent Builder
     *
     * @var string|EloquentBuilder|null
     */
    protected string|EloquentBuilder|null $model = null;

    /**
     * Allowed sortable fields
     *
     * @var array
     */
    protected array $allowedSorts = [];

    /**
     * Default sort fields
     *
     * @var array|string|null
     */
    protected array|string|null $defaultSort = null;

    /**
     * Allowed fields for selection
     *
     * @var array
     */
    protected array $allowedFields = [];

    /**
     * Allowed filters
     *
     * @var array
     */
    protected array $allowedFilters = [];

    /**
     * Allowed includes (relationships)
     *
     * @var array
     */
    protected array $allowedIncludes = [];

    /**
     * QueryAPI Config class
     *
     * @var string|null
     */
    protected ?string $configClass = null;

    /**
     * Query Builder Instance
     *
     * @var QueryBuilder|null
     */
    protected ?QueryBuilder $queryBuilder = null;

    /**
     * Request Instance
     *
     * @var Request|null
     */
    protected ?Request $request = null;

    /**
     * Virtual fields (fields that are not database columns)
     *
     * @var array
     */
    protected array $virtualFields = [];

    /**
     * Cache for model table names to improve performance
     *
     * @var array
     */
    protected static array $modelTableCache = [];

    /**
     * Set the target model class or Eloquent Builder
     *
     * @param string|EloquentBuilder $model
     * @return self
     */
    public function for(string|EloquentBuilder $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set allowed sortable fields
     *
     * @param array|string $sorts
     * @return self
     */
    public function sort(array|string $sorts): self
    {
        $this->allowedSorts = is_array($sorts) ? $sorts : [$sorts];
        return $this;
    }

    /**
     * Set default sort fields
     *
     * @param array|string $sorts
     * @return self
     */
    public function defaultSort(array|string $sorts): self
    {
        $this->defaultSort = $sorts;
        return $this;
    }

    /**
     * Set allowed fields for selection
     *
     * @param array|string $fields
     * @return self
     */
    public function fields(array|string $fields): self
    {
        $this->allowedFields = is_array($fields) ? $fields : [$fields];
        return $this;
    }

    /**
     * Set allowed filters
     *
     * @param array|string $filters
     * @return self
     */
    public function filters(array|string $filters): self
    {
        $this->allowedFilters = is_array($filters) ? $filters : [$filters];
        return $this;
    }

    /**
     * Set allowed includes (relationships)
     *
     * @param array|string $includes
     * @return self
     */
    public function includes(array|string $includes): self
    {
        $this->allowedIncludes = is_array($includes) ? $includes : [$includes];
        return $this;
    }

    /**
     * Set QueryAPI config class
     *
     * @param string $configClass
     * @return self
     */
    public function config(string $configClass): self
    {
        if (!is_subclass_of($configClass, QueryAPIConfig::class)) {
            throw new \InvalidArgumentException("Config class must extend " . QueryAPIConfig::class);
        }

        $this->configClass = $configClass;
        return $this;
    }

    /**
     * Build Query Builder with all constraints
     *
     * @param Request $request
     * @return QueryBuilder
     */
    protected function buildQuery(Request $request): QueryBuilder
    {
        $this->request = $request;

        // Get fields from config class if exists and not manually set
        if ($this->configClass && empty($this->allowedFields)) {
            $configFields = $this->configClass::fields();
            if ($configFields !== null) {
                $this->allowedFields = is_array($configFields) ? $configFields : [$configFields];
            }
        }

        // Get virtual fields from config class if exists
        if ($this->configClass && method_exists($this->configClass, 'virtualFields')) {
            $configVirtualFields = $this->configClass::virtualFields();
            if ($configVirtualFields !== null) {
                $this->virtualFields = is_array($configVirtualFields) ? $configVirtualFields : [$configVirtualFields];
            }
        }

        // Validate requested fields against allowed fields (including virtual fields)
        $this->validateRequestedFields($request);

        // Remove virtual fields from Request before passing to Spatie QueryBuilder
        $requestForSpatie = $this->removeVirtualFieldsFromRequest($request);

        // Create Query Builder from model or Eloquent Builder with modified request
        $query = QueryBuilder::for($this->model, $requestForSpatie);

        // Remove virtual fields from allowedFields before passing to Spatie QueryBuilder
        $fieldsForSpatie = array_diff($this->allowedFields, $this->virtualFields);

        // Get sorts from config class if exists and not manually set
        if ($this->configClass && empty($this->allowedSorts)) {
            $configSorts = $this->configClass::sorts();
            if ($configSorts !== null) {
                $this->allowedSorts = is_array($configSorts) ? $configSorts : [$configSorts];
            }
        }

        // Get default sort from config class if exists and not manually set
        if ($this->configClass && $this->defaultSort === null) {
            $this->defaultSort = $this->configClass::defaultSort();
        }

        // Get filters from config class if exists and not manually set
        if ($this->configClass && empty($this->allowedFilters)) {
            if (method_exists($this->configClass, 'filters')) {
                $configFilters = $this->configClass::filters();
                if ($configFilters !== null) {
                    $this->allowedFilters = is_array($configFilters) ? $configFilters : [$configFilters];
                }
            }
        }

        // Get includes from config class if exists and not manually set
        if ($this->configClass && empty($this->allowedIncludes)) {
            if (method_exists($this->configClass, 'includes')) {
                $configIncludes = $this->configClass::includes();
                if ($configIncludes !== null) {
                    $this->allowedIncludes = is_array($configIncludes) ? $configIncludes : [$configIncludes];
                }
            }
        }

        // Add allowed fields (must be called before allowedIncludes)
        // Only pass non-virtual fields to Spatie QueryBuilder
        if (!empty($fieldsForSpatie)) {
            $query->allowedFields($fieldsForSpatie);
        }

        // Add allowed includes (must be called after allowedFields)
        if (!empty($this->allowedIncludes)) {
            $query->allowedIncludes($this->allowedIncludes);
        }

        // Add allowed filters
        if (!empty($this->allowedFilters)) {
            $query->allowedFilters($this->allowedFilters);
        }

        // Add allowed sorts
        if (!empty($this->allowedSorts)) {
            $query->allowedSorts($this->allowedSorts);
        }

        // Add default sort
        if ($this->defaultSort !== null) {
            if (is_array($this->defaultSort)) {
                $query->defaultSort(...$this->defaultSort);
            } else {
                $query->defaultSort($this->defaultSort);
            }
        }

        $this->queryBuilder = $query;

        return $query;
    }


    /**
     * Get all data
     *
     * @return Collection
     */
    public function get(): Collection
    {
        $request = request();
        $query = $this->buildQuery($request);
        
        return $query->get();
    }

    /**
     * Get data with pagination
     *
     * @param int|null $perPage If null, uses defaultPerPage() from config
     * @return LengthAwarePaginator
     */
    public function paginate(?int $perPage = null): LengthAwarePaginator
    {
        $request = request();
        $query = $this->buildQuery($request);
        
        // Get pagination limits from config class if exists
        $defaultPerPage = 20;
        $minPerPage = 1;
        $maxPerPage = 100;
        
        if ($this->configClass) {
            if (method_exists($this->configClass, 'defaultPerPage')) {
                $defaultPerPage = $this->configClass::defaultPerPage();
            }
            if (method_exists($this->configClass, 'minPerPage')) {
                $minPerPage = $this->configClass::minPerPage();
            }
            if (method_exists($this->configClass, 'maxPerPage')) {
                $maxPerPage = $this->configClass::maxPerPage();
            }
        }
        
        // Use provided perPage, or from request, or default from config
        $perPage = $perPage ?? $request->input('per_page', $defaultPerPage);
        $perPage = (int) $perPage;
        
        // Apply min and max limits
        $perPage = max($minPerPage, min($perPage, $maxPerPage));

        return $query->paginate($perPage);
    }

    /**
     * Get all data, but allow pagination via request parameter
     * 
     * Default behavior is to return all data (get()).
     * If 'paginate' parameter exists in request, uses pagination instead.
     * 
     * @return Collection|LengthAwarePaginator
     */
    public function getOrPaginate(): Collection|LengthAwarePaginator
    {
        $request = request();
        
        // Check if paginate parameter exists
        if ($request->has('paginate') && $request->boolean('paginate')) {
            return $this->paginate();
        }
        
        return $this->get();
    }

    /**
     * Get paginated data, but allow get all via request parameter
     * 
     * Default behavior is to return paginated data.
     * If 'get' parameter exists in request, returns all data instead.
     * 
     * @return Collection|LengthAwarePaginator
     */
    public function paginateOrGet(): Collection|LengthAwarePaginator
    {
        $request = request();
        
        // Check if get parameter exists
        if ($request->has('get') && $request->boolean('get')) {
            return $this->get();
        }
        
        return $this->paginate();
    }

    /**
     * Get model information (name, table name, plural, singular)
     * Uses caching to improve performance
     *
     * @return array{modelName: string, modelTableName: string, modelNamePlural: string, modelNameSingular: string}
     */
    protected function getModelInfo(): array
    {
        $modelName = 'model';
        $modelTableName = 'model';
        
        if ($this->configClass) {
            try {
                $modelClass = $this->configClass::model();
                $modelName = class_basename($modelClass);
                $modelName = strtolower($modelName);
                
                // Get table name from cache or model
                if (!isset(self::$modelTableCache[$modelClass])) {
                    if (method_exists($modelClass, 'getTable')) {
                        // Use static method if available (Laravel 11+)
                        if (method_exists($modelClass, 'make')) {
                            self::$modelTableCache[$modelClass] = (new $modelClass())->getTable();
                        } else {
                            // Fallback for older Laravel versions
                            self::$modelTableCache[$modelClass] = (new $modelClass())->getTable();
                        }
                    } else {
                        self::$modelTableCache[$modelClass] = $modelName;
                    }
                }
                
                $modelTableName = self::$modelTableCache[$modelClass];
            } catch (\Throwable $e) {
                // If model class doesn't exist or can't be instantiated, use defaults
                $modelName = 'model';
                $modelTableName = 'model';
            }
        }
        
        return [
            'modelName' => $modelName,
            'modelTableName' => $modelTableName,
            'modelNamePlural' => \Illuminate\Support\Str::plural($modelName),
            'modelNameSingular' => \Illuminate\Support\Str::singular($modelName),
        ];
    }

    /**
     * Remove virtual fields from Request before passing to Spatie QueryBuilder
     *
     * @param Request $request
     * @return Request
     */
    protected function removeVirtualFieldsFromRequest(Request $request): Request
    {
        // If no virtual fields, return original request
        if (empty($this->virtualFields)) {
            return $request;
        }

        // Get model information using shared method
        $modelInfo = $this->getModelInfo();
        $modelName = $modelInfo['modelName'];
        $modelTableName = $modelInfo['modelTableName'];
        $modelNamePlural = $modelInfo['modelNamePlural'];
        $modelNameSingular = $modelInfo['modelNameSingular'];

        // Get requested fields from query parameter
        $requestedFields = $request->input('fields', []);
        
        // If no fields requested, return original request
        if (empty($requestedFields)) {
            return $request;
        }

        // Create new request with modified query parameters
        $queryParams = $request->query->all();
        
        // Handle different field formats
        if (is_string($requestedFields)) {
            // Format: fields=field1,field2 (treated as '_' by Spatie)
            $modelFields = explode(',', $requestedFields);
            $filteredFields = array_values(array_filter($modelFields, function($field) {
                $field = trim($field);
                return !in_array($field, $this->virtualFields);
            }));
            
            if (empty($filteredFields)) {
                unset($queryParams['fields']);
            } else {
                $queryParams['fields'] = implode(',', $filteredFields);
            }
        } elseif (is_array($requestedFields)) {
            // Format: fields[model]=field1,field2 or fields[_]=field1,field2
            // Check all possible field keys: table name, model name (singular/plural), and underscore
            
            $keysToCheck = array_unique([
                $modelTableName,  // e.g., 'settings'
                $modelName,       // e.g., 'setting'
                $modelNamePlural, // e.g., 'settings'
                $modelNameSingular, // e.g., 'setting'
                '_',              // default format
            ]);
            
            foreach ($keysToCheck as $key) {
                if (isset($requestedFields[$key])) {
                    $modelFields = is_string($requestedFields[$key]) 
                        ? explode(',', $requestedFields[$key]) 
                        : $requestedFields[$key];
                    
                    $filteredFields = array_values(array_filter($modelFields, function($field) {
                        $field = trim($field);
                        return !in_array($field, $this->virtualFields);
                    }));
                    
                    if (empty($filteredFields)) {
                        unset($queryParams['fields'][$key]);
                    } else {
                        $queryParams['fields'][$key] = implode(',', $filteredFields);
                    }
                }
            }
            
            // If fields array is empty, remove it completely
            if (isset($queryParams['fields']) && is_array($queryParams['fields']) && empty($queryParams['fields'])) {
                unset($queryParams['fields']);
            }
        }

        // Create new request with modified query parameters
        $modifiedRequest = Request::create(
            $request->url(),
            $request->method(),
            $request->request->all(),
            $queryParams,
            $request->files->all(),
            $request->server->all(),
            $request->getContent()
        );

        return $modifiedRequest;
    }

    /**
     * Validate requested fields against allowed fields (including virtual fields)
     *
     * @param Request $request
     * @return void
     */
    protected function validateRequestedFields(Request $request): void
    {
        // Get requested fields from query parameter
        $requestedFields = $request->input('fields', []);
        
        // If no fields requested, skip validation
        if (empty($requestedFields)) {
            return;
        }

        // Get model information using shared method
        $modelInfo = $this->getModelInfo();
        $modelName = $modelInfo['modelName'];
        $modelTableName = $modelInfo['modelTableName'];
        $modelNamePlural = $modelInfo['modelNamePlural'];
        $modelNameSingular = $modelInfo['modelNameSingular'];

        // Get requested fields for this model
        $modelFields = [];
        if (is_string($requestedFields)) {
            // Format: fields=field1,field2 (treated as '_' by Spatie)
            $modelFields = explode(',', $requestedFields);
        } elseif (is_array($requestedFields)) {
            // Format: fields[model]=field1,field2 or fields[_]=field1,field2
            // Check all possible field keys: table name, model name (singular/plural), and underscore
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
            
            // If no model-specific fields found, skip validation
            if (empty($modelFields)) {
                return;
            }
        } else {
            // If no model-specific fields, skip validation
            return;
        }

        // Combine allowed fields and virtual fields for validation
        $allAllowedFields = array_merge($this->allowedFields, $this->virtualFields);

        // Validate each requested field
        $unknownFields = [];
        foreach ($modelFields as $field) {
            $field = trim($field);
            if (!empty($field) && !in_array($field, $allAllowedFields)) {
                $unknownFields[] = "{$modelName}.{$field}";
            }
        }
        
        // Throw exception if there are unknown fields
        if (!empty($unknownFields)) {
            $allowedFieldsList = array_map(function($f) use ($modelName) {
                return "{$modelName}.{$f}";
            }, $allAllowedFields);
            
            throw \Spatie\QueryBuilder\Exceptions\InvalidFieldQuery::fieldsNotAllowed(
                \Illuminate\Support\Collection::make($unknownFields),
                \Illuminate\Support\Collection::make($allowedFieldsList)
            );
        }
    }

    /**
     * Clear model table cache
     * Useful for testing or when models are dynamically changed
     *
     * @return void
     */
    public static function clearModelTableCache(): void
    {
        self::$modelTableCache = [];
    }

    /**
     * Reset all settings
     *
     * @return self
     */
    public function reset(): self
    {
        $this->model = null;
        $this->allowedSorts = [];
        $this->allowedFields = [];
        $this->virtualFields = [];
        $this->defaultSort = null;
        $this->configClass = null;
        $this->queryBuilder = null;
        $this->request = null;

        return $this;
    }
}
