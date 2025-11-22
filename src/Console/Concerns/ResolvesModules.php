<?php

namespace Rawnoq\QueryAPI\Console\Concerns;

use Illuminate\Support\Str;

/**
 * Trait ResolvesModules
 * 
 * Helper trait for resolving module information
 * Compatible with rawnoq/laravel-hmvc package
 * 
 * @package Rawnoq\QueryAPI\Console\Concerns
 */
trait ResolvesModules
{
    /**
     * Current module name being processed
     *
     * @var string|null
     */
    protected ?string $moduleName = null;

    /**
     * Normalize module name
     *
     * @param string $module
     * @return string
     */
    protected function normalizeModule(string $module): string
    {
        return Str::studly($module);
    }

    /**
     * Check if module exists
     *
     * @param string $module
     * @return bool
     */
    protected function moduleExists(string $module): bool
    {
        $modulePath = $this->moduleBasePath($module);

        return is_dir($modulePath);
    }

    /**
     * Get module root namespace
     *
     * @param string $module
     * @param bool $includeAppSegment
     * @return string
     */
    protected function moduleRootNamespace(string $module, bool $includeAppSegment = true): string
    {
        $namespace = config('hmvc.namespace', 'Modules').'\\'.$module.'\\';

        if ($includeAppSegment) {
            $namespace .= 'App\\';
        }

        return $namespace;
    }

    /**
     * Get module base path
     *
     * @param string $module
     * @return string
     */
    protected function moduleBasePath(string $module): string
    {
        $modulesPath = config('hmvc.modules_path', base_path('modules'));

        return $modulesPath.DIRECTORY_SEPARATOR.$module;
    }

    /**
     * Get module primary directory for a specific type
     *
     * @param string $module
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function modulePrimaryDirectory(string $module, string $key, string $default): string
    {
        $directories = config("hmvc.directories.{$key}", [$default]);

        return is_array($directories) ? ($directories[0] ?? $default) : $default;
    }
}

