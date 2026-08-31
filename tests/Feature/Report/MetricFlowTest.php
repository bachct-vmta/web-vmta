<?php

namespace Tests\Feature\Report;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Report\Src\Facades\Report;
use Packages\Report\Src\Models\MetricDaily;
use Packages\Report\Src\Repositories\MetricRepository;
use Tests\TestCase;

class MetricFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_increment_writes_directly_to_metric_daily_when_redis_disabled(): void
    {
        Report::increment('lead.contact_form', 3);
        Report::increment('lead.total', 3);

        $this->assertSame(3, MetricDaily::where('metric_key', 'lead.contact_form')->value('count'));
        $this->assertSame(3, MetricDaily::where('metric_key', 'lead.total')->value('count'));
    }

    public function test_repeated_increment_accumulates(): void
    {
        Report::increment('pageview.total');
        Report::increment('pageview.total', 4);
        Report::increment('pageview.total');

        $this->assertSame(6, MetricDaily::where('metric_key', 'pageview.total')->value('count'));
    }

    public function test_daily_series_returns_30_days_with_zeros_filled(): void
    {
        Report::increment('pageview.total', 5);

        $series = app(MetricRepository::class)->dailySeries('pageview.total', 30);
        $this->assertCount(30, $series);
        $this->assertSame(5, $series->last());
    }

    public function test_top_by_pattern_orders_by_count_desc(): void
    {
        Report::increment('pageview.page.1', 10);
        Report::increment('pageview.page.2', 3);
        Report::increment('pageview.page.3', 7);

        $top = app(MetricRepository::class)->topByPattern('pageview.page.%', 5);
        $this->assertSame(['pageview.page.1', 'pageview.page.3', 'pageview.page.2'], $top->keys()->all());
        $this->assertSame([10, 7, 3], $top->values()->all());
    }

    public function test_sum_by_prefix_excludes_total_key(): void
    {
        Report::increment('lead.total', 5);
        Report::increment('lead.contact_form', 3);
        Report::increment('lead.emergency', 2);

        $breakdown = app(MetricRepository::class)->sumByPrefix('lead.');
        $this->assertSame(['contact_form' => 3, 'emergency' => 2], $breakdown->all());
    }

    public function test_dashboard_renders_for_admin(): void
    {
        $this->seed(\Packages\Core\Database\Seeders\AdminSeeder::class);
        $admin = \Packages\Core\Src\Models\User::where('email', 'admin@nguyenkhoi.dev')->first();

        Report::increment('lead.total', 3);
        Report::increment('pageview.total', 10);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Pageview hôm nay', false);
        $response->assertSee('Lead 7 ngày', false);
    }
}
