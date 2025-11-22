<?php

namespace Rawnoq\QueryAPI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * QueryAPI Facade
 * 
 * Facade for easy access to QueryAPI Service
 * 
 * @method static \Rawnoq\QueryAPI\QueryAPI for(string|\Illuminate\Database\Eloquent\Builder $model)
 * @method static \Rawnoq\QueryAPI\QueryAPI fields(array|string $fields)
 * @method static \Rawnoq\QueryAPI\QueryAPI filters(array|string $filters)
 * @method static \Rawnoq\QueryAPI\QueryAPI includes(array|string $includes)
 * @method static \Rawnoq\QueryAPI\QueryAPI sort(array|string $sorts)
 * @method static \Rawnoq\QueryAPI\QueryAPI defaultSort(array|string $sorts)
 * @method static \Rawnoq\QueryAPI\QueryAPI config(string $configClass)
 * @method static \Illuminate\Support\Collection get()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate(int $perPage = 20)
 * @method static \Rawnoq\QueryAPI\QueryAPI reset()
 * 
 * @see \Rawnoq\QueryAPI\QueryAPI
 */
class QueryAPI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'query-api';
    }
}

