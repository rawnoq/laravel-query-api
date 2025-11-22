<?php

namespace Rawnoq\QueryAPI\Enums;

use Spatie\QueryBuilder\Enums\FilterOperator as SpatieFilterOperator;

/**
 * FilterOperator Class
 * 
 * Wrapper for Spatie FilterOperator enum for easier access
 * Re-exports all filter operator constants from Spatie
 * 
 * @package Rawnoq\QueryAPI\Enums
 */
class FilterOperator
{
    /**
     * Equal operator (=)
     */
    public const EQUAL = SpatieFilterOperator::EQUAL;

    /**
     * Not equal operator (!=)
     */
    public const NOT_EQUAL = SpatieFilterOperator::NOT_EQUAL;

    /**
     * Greater than operator (>)
     */
    public const GREATER_THAN = SpatieFilterOperator::GREATER_THAN;

    /**
     * Less than operator (<)
     */
    public const LESS_THAN = SpatieFilterOperator::LESS_THAN;

    /**
     * Greater than or equal operator (>=)
     */
    public const GREATER_THAN_OR_EQUAL = SpatieFilterOperator::GREATER_THAN_OR_EQUAL;

    /**
     * Less than or equal operator (<=)
     */
    public const LESS_THAN_OR_EQUAL = SpatieFilterOperator::LESS_THAN_OR_EQUAL;

    /**
     * Dynamic operator - allows using any operator in query string
     * Example: ?filter[salary]=>3000 or ?filter[price]=<100
     */
    public const DYNAMIC = SpatieFilterOperator::DYNAMIC;
}

