<?php

namespace Rawnoq\QueryAPI\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Rawnoq\QueryAPI\Console\Concerns\ResolvesModules;
use Symfony\Component\Console\Input\InputOption;

/**
 * Make QueryAPI Config Command
 * 
 * Artisan command to generate QueryAPIConfig classes
 * 
 * @package Rawnoq\QueryAPI\Console
 */
class MakeQueryAPICommand extends GeneratorCommand
{
    use ResolvesModules;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:query-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new QueryAPI configuration class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'QueryAPI';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/query-api.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace(): string
    {
        if ($this->moduleName) {
            return $this->moduleRootNamespace($this->moduleName);
        }

        return parent::rootNamespace();
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->moduleName) {
            return rtrim($rootNamespace, '\\').'\\QueryAPI';
        }

        return rtrim($rootNamespace, '\\').'\\QueryAPI';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name): string
    {
        if ($this->moduleName) {
            $parentPath = parent::getPath($name);
            $appPath = app_path().DIRECTORY_SEPARATOR;
            $relative = Str::after($parentPath, $appPath);

            if ($relative === $parentPath) {
                $relative = basename($parentPath);
            }

            $relative = ltrim($relative, DIRECTORY_SEPARATOR);

            if (Str::startsWith($relative, 'QueryAPI'.DIRECTORY_SEPARATOR)) {
                $relative = Str::after($relative, 'QueryAPI'.DIRECTORY_SEPARATOR);
            }

            return $this->moduleBasePath($this->moduleName)
                .DIRECTORY_SEPARATOR.'App'
                .DIRECTORY_SEPARATOR.'QueryAPI'
                .DIRECTORY_SEPARATOR.$relative;
        }

        return parent::getPath($name);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $moduleOption = $this->option('module');

        if (! $moduleOption) {
            $result = parent::handle();

            $this->moduleName = null;

            return is_int($result) ? $result : self::SUCCESS;
        }

        $this->moduleName = $this->normalizeModule($moduleOption);

        if (! $this->moduleExists($this->moduleName)) {
            $this->components->error("Module [{$this->moduleName}] does not exist.");
            $this->moduleName = null;

            return self::FAILURE;
        }

        $result = parent::handle();

        $this->moduleName = null;

        return is_int($result) ? $result : self::SUCCESS;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $this->replaceModel($stub);

        return $stub;
    }

    /**
     * Replace the model for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceModel(string &$stub): static
    {
        $model = $this->option('model');

        if (!$model) {
            // Try to guess model from class name
            // UserQueryAPI -> User
            $className = class_basename($this->argument('name'));
            $model = str_replace('QueryAPI', '', $className);
        }

        $modelClass = $this->qualifyModel($model);
        $modelVariable = Str::camel($model);
        $modelName = class_basename($modelClass);

        $stub = str_replace(
            ['DummyModelClass', '{{ modelClass }}', '{{modelClass}}'],
            $modelClass,
            $stub
        );

        $stub = str_replace(
            ['DummyModelVariable', '{{ modelVariable }}', '{{modelVariable}}'],
            $modelVariable,
            $stub
        );

        $stub = str_replace(
            ['DummyModel', '{{ model }}', '{{model}}'],
            $modelName,
            $stub
        );

        return $this;
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
     * @return string
     */
    protected function qualifyModel(string $model): string
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        // If module is being used, use module namespace
        if ($this->moduleName) {
            // Check if model already has full namespace
            if (Str::startsWith($model, 'Modules\\')) {
                return $model;
            }

            // Check if model already has module prefix
            if (Str::startsWith($model, $this->moduleName.'\\')) {
                return "Modules\\{$model}";
            }

            return $this->moduleRootNamespace($this->moduleName).'Models\\'.$model;
        }

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
            ? $rootNamespace.'Models\\'.$model
            : $rootNamespace.$model;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that this QueryAPI config is for'],
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module that this QueryAPI config belongs to'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the QueryAPI config already exists'],
        ];
    }
}

