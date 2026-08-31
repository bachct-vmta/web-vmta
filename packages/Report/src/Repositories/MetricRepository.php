<?php

namespace Packages\Report\Src\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Report\Src\Models\MetricDaily;

class MetricRepository
{
    /**
     * Atomic upsert: increment count for (date, metric_key) by $delta.
     * Composite-PK safe on MySQL/PostgreSQL/SQLite.
     */
    public function increment(string $date, string $key, int $delta = 1, ?array $meta = null): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                'INSERT INTO metric_daily (date, metric_key, count, meta, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE count = count + VALUES(count), updated_at = NOW()',
                [$date, $key, $delta, $meta !== null ? json_encode($meta) : null],
            );

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement(
                'INSERT INTO metric_daily (date, metric_key, count, meta, created_at, updated_at) VALUES (?, ?, ?, ?::jsonb, NOW(), NOW())
                 ON CONFLICT (date, metric_key) DO UPDATE SET count = metric_daily.count + EXCLUDED.count, updated_at = NOW()',
                [$date, $key, $delta, $meta !== null ? json_encode($meta) : null],
            );

            return;
        }

        // SQLite + others — use upsert.
        DB::transaction(function () use ($date, $key, $delta, $meta) {
            $existing = MetricDaily::where('date', $date)->where('metric_key', $key)->lockForUpdate()->first();
            if ($existing) {
                $existing->increment('count', $delta);

                return;
            }

            MetricDaily::create([
                'date' => $date,
                'metric_key' => $key,
                'count' => $delta,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Sum count per date for a single key over the last $days days.
     * Returns Collection<string date => int count>, dates filled including zeros.
     */
    public function dailySeries(string $key, int $days = 30): Collection
    {
        $start = Carbon::today()->subDays($days - 1);

        $rows = MetricDaily::where('metric_key', $key)
            ->whereDate('date', '>=', $start)
            ->orderBy('date')
            ->pluck('count', 'date');

        // Convert Carbon-cast dates back to Y-m-d strings.
        $keyed = collect();
        foreach ($rows as $date => $count) {
            $iso = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date;
            $keyed->put($iso, (int) $count);
        }

        $series = collect();
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i)->toDateString();
            $series->put($d, (int) ($keyed->get($d) ?? 0));
        }

        return $series;
    }

    /**
     * Sum across a date range for a single key. Default = today.
     */
    public function sumRange(string $key, ?string $from = null, ?string $to = null): int
    {
        $from = $from ?: Carbon::today()->toDateString();
        $to = $to ?: Carbon::today()->toDateString();

        return (int) MetricDaily::where('metric_key', $key)
            ->whereBetween('date', [$from, $to])
            ->sum('count');
    }

    /**
     * Sum across the keys matching a LIKE pattern over a date range.
     * Returns Collection<key => count>, sorted desc by count, limited.
     */
    public function topByPattern(string $pattern, int $limit = 10, ?string $from = null, ?string $to = null): Collection
    {
        $from = $from ?: Carbon::today()->subDays(29)->toDateString();
        $to = $to ?: Carbon::today()->toDateString();

        return MetricDaily::where('metric_key', 'like', $pattern)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('metric_key, SUM(count) as total')
            ->groupBy('metric_key')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->pluck('total', 'metric_key')
            ->map(fn ($v) => (int) $v);
    }

    /**
     * Pre-grouped sum of keys with the given prefix; returns Collection<suffix => total>.
     * e.g. prefix='lead.' returns ['contact_form'=>X, 'emergency'=>Y, ...]
     */
    public function sumByPrefix(string $prefix, ?string $from = null, ?string $to = null): Collection
    {
        $from = $from ?: Carbon::today()->subDays(29)->toDateString();
        $to = $to ?: Carbon::today()->toDateString();

        return MetricDaily::where('metric_key', 'like', $prefix.'%')
            ->where('metric_key', '!=', $prefix.'total')
            ->whereBetween('date', [$from, $to])
            ->selectRaw('metric_key, SUM(count) as total')
            ->groupBy('metric_key')
            ->get()
            ->mapWithKeys(fn ($r) => [substr($r->metric_key, strlen($prefix)) => (int) $r->total]);
    }
}
