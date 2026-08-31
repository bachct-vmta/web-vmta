<?php

namespace Packages\Report\Src\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void increment(string $key, int $delta = 1, ?array $meta = null)
 * @method static int flushBuckets()
 *
 * @see \Packages\Report\Src\Services\MetricService
 */
class Report extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Packages\Report\Src\Services\MetricService::class;
    }
}
