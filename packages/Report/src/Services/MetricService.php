<?php

namespace Packages\Report\Src\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Packages\Report\Src\Repositories\MetricRepository;

/**
 * Non-blocking metric increment.
 *
 * Strategy: when Redis is configured, increment a per-day Redis bucket key
 *   metric:bucket:{date}:{key} (atomic INCRBY). A scheduled flush command
 *   atomically RENAMEs the bucket → reads → UPSERTs metric_daily → deletes.
 *
 * Fallback: when Redis is unreachable / not configured, write straight to
 *   metric_daily via the repository. Slower but correct — used in dev/test.
 */
class MetricService
{
    public function __construct(private readonly MetricRepository $repository) {}

    /**
     * Bucket key prefix kept stable so flush can scan deterministically.
     */
    public const BUCKET_PREFIX = 'metric:bucket:';

    public const DATE_INDEX_KEY = 'metric:bucket:dates';

    public function increment(string $key, int $delta = 1, ?array $meta = null): void
    {
        if ($delta <= 0) {
            return;
        }

        $date = Carbon::today()->toDateString();

        if ($this->shouldUseRedis()) {
            try {
                $bucket = self::BUCKET_PREFIX.$date;
                Redis::hincrby($bucket, $key, $delta);
                // Auto-expire after 48h so abandoned dates can't accumulate forever.
                Redis::expire($bucket, 172800);
                // Track which dates have pending buckets so flush doesn't need SCAN
                // (Laravel's Redis key prefix breaks SCAN match patterns).
                Redis::sadd(self::DATE_INDEX_KEY, $date);

                return;
            } catch (\Throwable $e) {
                Log::warning('report.metric.redis_increment_failed', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
                // fall through to DB
            }
        }

        try {
            $this->repository->increment($date, $key, $delta, $meta);
        } catch (\Throwable $e) {
            Log::error('report.metric.db_increment_failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Drain every Redis bucket into metric_daily. Returns number of (date,key) pairs flushed.
     * Safe to run concurrently: RENAME atomically claims the bucket before reading.
     */
    public function flushBuckets(): int
    {
        if (! $this->shouldUseRedis()) {
            return 0;
        }

        $flushed = 0;

        try {
            $dates = (array) Redis::smembers(self::DATE_INDEX_KEY);
        } catch (\Throwable $e) {
            Log::error('report.metric.flush_index_read_failed', ['error' => $e->getMessage()]);

            return 0;
        }

        foreach ($dates as $date) {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
                continue;
            }

            $bucketKey = self::BUCKET_PREFIX.$date;
            $claimed = $bucketKey.':flushing:'.uniqid('', true);

            try {
                // RENAME atomically transfers ownership; if another worker beat us, it errors.
                Redis::rename($bucketKey, $claimed);
            } catch (\Throwable) {
                // Either another worker claimed it, or no bucket existed.
                // Remove stale date entry only if no bucket remains.
                try {
                    if (! Redis::exists($bucketKey) && ! Redis::exists($claimed)) {
                        Redis::srem(self::DATE_INDEX_KEY, $date);
                    }
                } catch (\Throwable) {
                    // best effort
                }
                continue;
            }

            try {
                $entries = Redis::hgetall($claimed);
                foreach ($entries as $metricKey => $delta) {
                    $this->repository->increment($date, (string) $metricKey, (int) $delta);
                    $flushed++;
                }
            } catch (\Throwable $e) {
                Log::error('report.metric.flush_upsert_failed', [
                    'bucket' => $claimed,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                try {
                    Redis::del($claimed);
                    Redis::srem(self::DATE_INDEX_KEY, $date);
                } catch (\Throwable) {
                    // best effort
                }
            }
        }

        return $flushed;
    }

    protected function shouldUseRedis(): bool
    {
        // CACHE_STORE / REDIS_CLIENT presence isn't a guarantee, but extension or predis must be loaded.
        if (! (extension_loaded('redis') || class_exists(\Predis\Client::class))) {
            return false;
        }

        return config('report.use_redis', true);
    }

}
