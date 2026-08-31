<?php

namespace Packages\Report\Src\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Report\Src\Repositories\MetricRepository;
use Packages\Report\Src\Services\MetricService;

class DashboardController extends BaseController
{
    public function __construct(
        private readonly MetricRepository $repository,
        private readonly MetricService $metricService,
    ) {}

    public function index(Request $request)
    {
        if ($request->boolean('refresh')) {
            Cache::forget('report.dashboard');
        }

        // Drain Redis buckets synchronously so dashboard always shows current
        // counts even when the `metrics:flush` scheduler isn't running (dev,
        // missing cron, etc.). Cheap — only flushes a few hash keys per date.
        try {
            $this->metricService->flushBuckets();
        } catch (\Throwable) {
            // Best-effort — dashboard still renders from DB if flush fails.
        }

        $data = Cache::remember('report.dashboard', (int) config('report.dashboard_cache_ttl', 300), function () {
            $today = Carbon::today()->toDateString();
            $weekFrom = Carbon::today()->subDays(6)->toDateString();
            $monthFrom = Carbon::today()->subDays(29)->toDateString();

            return [
                'pageview_today' => $this->repository->sumRange('pageview.total', $today, $today),
                'lead_7d' => $this->repository->sumRange('lead.total', $weekFrom, $today),
                'chatbot_sessions_7d' => $this->repository->sumRange('chatbot.sessions_started', $weekFrom, $today),
                'chatbot_messages_7d' => $this->repository->sumRange('chatbot.messages_sent', $weekFrom, $today),

                'pageview_series' => $this->repository->dailySeries('pageview.total', 30),
                'lead_series' => $this->repository->dailySeries('lead.total', 30),
                'chatbot_series' => $this->repository->dailySeries('chatbot.messages_sent', 30),

                'top_pages' => $this->repository->topByPattern('pageview.%', 10, $monthFrom, $today),
                'lead_breakdown' => $this->repository->sumByPrefix('lead.', $monthFrom, $today),
            ];
        });

        return view('report::admin.dashboard', $data);
    }
}
