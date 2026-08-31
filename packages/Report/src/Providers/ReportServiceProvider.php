<?php

namespace Packages\Report\Src\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Report\Src\Console\Commands\FlushMetricBuckets;
use Packages\Report\Src\Repositories\MetricRepository;
use Packages\Report\Src\Services\MetricService;

class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../configs/report.php', 'report');

        $this->app->singleton(MetricRepository::class);
        $this->app->singleton(MetricService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'report');

        if ($this->app->runningInConsole()) {
            $this->commands([
                FlushMetricBuckets::class,
            ]);
        }
    }
}
