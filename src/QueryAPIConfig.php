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
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public static function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return query_api(static::model(), static::class)->paginate($perPage);
    }
}

