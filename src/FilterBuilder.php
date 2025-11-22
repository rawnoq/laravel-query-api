<?php

namespace Rawnoq\QueryAPI;

use Spatie\QueryBuilder\AllowedFilter;
use Rawnoq\QueryAPI\Enums\FilterOperator;

/**
 * FilterBuilder Class
 * 
 * Fluent API for building filters
 * 
 * @package Rawnoq\QueryAPI
 */
class FilterBuilder
{
    /**
     * Create an exact filter
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    public function exact(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::exact($name, $internalName, $addRelationConstraint);
    }

    /**
     * Create a partial filter (LIKE %value%)
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    public function partial(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::partial($name, $internalName, $addRelationConstraint);
    }

    /**
     * Create a scope filter
     * 
     * @param string $name
     * @param string|null $internalName
     * @return AllowedFilter
     */
    public function scope(string $name, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::scope($name, $internalName);
    }

    /**
     * Create a callback filter
     * 
     * @param string $name
     * @param callable $callback
     * @param string|null $internalName
     * @return AllowedFilter
     */
    public function callback(string $name, callable $callback, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::callback($name, $callback, $internalName);
    }

    /**
     * Create a trashed filter for soft deletes
     * 
     * @param string $name
     * @return AllowedFilter
     */
    public function trashed(string $name = 'trashed'): AllowedFilter
    {
        return AllowedFilter::trashed($name);
    }

    /**
     * Create a begins with filter (LIKE value%)
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    public function beginsWith(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::beginsWithStrict($name, $internalName, $addRelationConstraint);
    }

    /**
     * Create an ends with filter (LIKE %value)
     * 
     * @param string $name
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    public function endsWith(string $name, ?string $internalName = null, bool $addRelationConstraint = true): AllowedFilter
    {
        return AllowedFilter::endsWithStrict($name, $internalName, $addRelationConstraint);
    }

    /**
     * Create a custom filter
     * 
     * @param string $name
     * @param mixed $filterClass
     * @param string|null $internalName
     * @return AllowedFilter
     */
    public function custom(string $name, $filterClass, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::custom($name, $filterClass, $internalName);
    }

    /**
     * Create an operator filter
     * 
     * Supports operators like: GREATER_THAN, LESS_THAN, EQUAL, NOT_EQUAL, etc.
     * Use FilterOperator::DYNAMIC to allow dynamic operators in query string (e.g., >3000, <100)
     * 
     * @param string $name
     * @param FilterOperator|string $operator
     * @param string|null $internalName
     * @param bool $addRelationConstraint
     * @return AllowedFilter
     */
    public function operator(
        string $name, 
        FilterOperator|string $operator = FilterOperator::DYNAMIC,
        ?string $internalName = null, 
        bool $addRelationConstraint = true
    ): AllowedFilter
    {
        return AllowedFilter::operator($name, $operator, $internalName, $addRelationConstraint);
    }
}

