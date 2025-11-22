<?php

namespace Rawnoq\QueryAPI;

use Illuminate\Support\ServiceProvider;
use Rawnoq\QueryAPI\Console\MakeQueryAPICommand;

/**
 * QueryAPI Service Provider
 * 
 * Register QueryAPI Service and Facade
 * 
 * @package Rawnoq\QueryAPI
 */
class QueryAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register QueryAPI Service as singleton
        $this->app->singleton('query-api', function ($app) {
            return new QueryAPI();
        });

        // Bind Facade
        $this->app->alias('query-api', QueryAPI::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeQueryAPICommand::class,
            ]);

            $this->publishes([
                __DIR__.'/Console/stubs/query-api.stub' => base_path('stubs/query-api.stub'),
            ], 'query-api-stubs');
        }
    }
}

