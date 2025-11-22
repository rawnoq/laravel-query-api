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

        // Create Query Builder from model or Eloquent Builder
        $query = QueryBuilder::for($this->model, $request);

        // Get fields from config class if exists and not manually set
        if ($this->configClass && empty($this->allowedFields)) {
            $configFields = $this->configClass::fields();
            if ($configFields !== null) {
                $this->allowedFields = is_array($configFields) ? $configFields : [$configFields];
            }
        }

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
        if (!empty($this->allowedFields)) {
            $query->allowedFields($this->allowedFields);
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
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        $request = request();
        $query = $this->buildQuery($request);
        
        // Allow perPage from query parameter
        $perPage = $request->input('per_page', $perPage);
        $perPage = min(max(1, (int) $perPage), 100); // Max 100

        return $query->paginate($perPage);
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
        $this->defaultSort = null;
        $this->configClass = null;
        $this->queryBuilder = null;
        $this->request = null;

        return $this;
    }
}
