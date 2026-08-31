<?php

namespace Packages\Report\Src\Console\Commands;

use Illuminate\Console\Command;
use Packages\Report\Src\Services\MetricService;

class FlushMetricBuckets extends Command
{
    protected $signature = 'metrics:flush';

    protected $description = 'Drain Redis metric buckets into metric_daily table.';

    public function handle(MetricService $service): int
    {
        $flushed = $service->flushBuckets();
        $this->info("Flushed {$flushed} metric entries.");

        return self::SUCCESS;
    }
}
