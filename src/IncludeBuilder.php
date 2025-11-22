<?php

namespace Rawnoq\QueryAPI;

use Spatie\QueryBuilder\AllowedInclude;

/**
 * IncludeBuilder Class
 * 
 * Fluent API for building includes (relationships)
 * 
 * @package Rawnoq\QueryAPI
 */
class IncludeBuilder
{
    /**
     * Create a relationship include
     * 
     * @param string $name The name of the relationship to include
     * @param string|null $internalName The internal name of the relationship (if different)
     * @return AllowedInclude
     */
    public function relationship(string $name, ?string $internalName = null): AllowedInclude
    {
        return AllowedInclude::relationship($name, $internalName);
    }

    /**
     * Create a count include
     * 
     * Allows including the count of related models (e.g., postsCount)
     * Uses Laravel's withCount method under the hood
     * 
     * @param string $name The name for the count (e.g., 'postsCount')
     * @param string|null $internalName The internal relationship name
     * @return AllowedInclude
     */
    public function count(string $name, ?string $internalName = null): AllowedInclude
    {
        return AllowedInclude::count($name, $internalName);
    }

    /**
     * Create an exists include
     * 
     * Allows checking if related models exist (e.g., postsExists)
     * Uses Laravel's withExists method under the hood
     * 
     * @param string $name The name for the exists check (e.g., 'postsExists')
     * @param string|null $internalName The internal relationship name
     * @return AllowedInclude
     */
    public function exists(string $name, ?string $internalName = null): AllowedInclude
    {
        return AllowedInclude::exists($name, $internalName);
    }

    /**
     * Create a callback include
     * 
     * Allows defining custom include logic using a closure
     * 
     * @param string $name The name of the include
     * @param callable $callback The callback to execute
     * @param string|null $internalName The internal name
     * @return AllowedInclude
     */
    public function callback(string $name, callable $callback, ?string $internalName = null): AllowedInclude
    {
        return AllowedInclude::callback($name, $callback, $internalName);
    }

    /**
     * Create a custom include
     * 
     * Allows using a custom include class that implements IncludeInterface
     * 
     * @param string $name The name of the include
     * @param mixed $includeClass The custom include class instance
     * @param string|null $internalName The internal name
     * @return AllowedInclude
     */
    public function custom(string $name, $includeClass, ?string $internalName = null): AllowedInclude
    {
        return AllowedInclude::custom($name, $includeClass, $internalName);
    }
}

