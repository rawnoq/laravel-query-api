<?php

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Spatie\QueryBuilder\AllowedFilter;

if (!function_exists('query_api')) {
    /**
     * Helper function to use QueryAPI
     * 
     * @param string|EloquentBuilder|null $model
     * @param string|null $configClass
     * @return \Rawnoq\QueryAPI\QueryAPI
     */
    function query_api(string|EloquentBuilder|null $model = null, ?string $configClass = null): \Rawnoq\QueryAPI\QueryAPI
    {
        $queryAPI = app('query-api');

        if ($model) {
            $queryAPI->for($model);
        }

        if ($configClass) {
            $queryAPI->config($configClass);
        }

        return $queryAPI;
    }
}

if (!function_exists('filter_exact')) {
    /**
     * Create an exact filter
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    function filter_exact(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::exact($name, $internalName, $addRelationConstraint);
    }
}

if (!function_exists('filter_partial')) {
    /**
     * Create a partial filter (LIKE %value%)
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    function filter_partial(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::partial($name, $internalName, $addRelationConstraint);
    }
}

if (!function_exists('filter_scope')) {
    /**
     * Create a scope filter
     * 
     * @param string $name
     * @param string|null $internalName
     * @return AllowedFilter
     */
    function filter_scope(string $name, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::scope($name, $internalName);
    }
}

if (!function_exists('filter_callback')) {
    /**
     * Create a callback filter
     * 
     * @param string $name
     * @param callable $callback
     * @param string|null $internalName
     * @return AllowedFilter
     */
    function filter_callback(string $name, callable $callback, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::callback($name, $callback, $internalName);
    }
}

if (!function_exists('filter_trashed')) {
    /**
     * Create a trashed filter for soft deletes
     * 
     * @param string $name
     * @return AllowedFilter
     */
    function filter_trashed(string $name = 'trashed'): AllowedFilter
    {
        return AllowedFilter::trashed($name);
    }
}

if (!function_exists('filter_begins_with')) {
    /**
     * Create a begins with filter (LIKE value%)
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    function filter_begins_with(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::beginsWithStrict($name, $internalName, $addRelationConstraint);
    }
}

if (!function_exists('filter_ends_with')) {
    /**
     * Create an ends with filter (LIKE %value)
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    function filter_ends_with(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::endsWithStrict($name, $internalName, $addRelationConstraint);
    }
}
