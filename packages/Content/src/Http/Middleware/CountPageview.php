<?php

namespace Packages\Content\Src\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packages\Report\Src\Facades\Report;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records a pageview for non-admin GET requests, plus a per-resource counter when
 * a Page/Post/Tour/Service/Combo model is bound on the route. Increments happen
 * after the response so a counter failure can never affect the page render.
 */
class CountPageview
{
    /**
     * URI prefixes that should be excluded from pageview counting.
     * Admin + API + livewire endpoints are infra, not content traffic.
     */
    private const SKIP_PREFIXES = ['admin', 'api', 'livewire', 'storage', 'sanctum', 'newsletter/confirm', 'newsletter/unsubscribe'];

    /**
     * Map model class → metric suffix key. New trackable types should be added here.
     */
    private const TRACKABLE = [
        \Packages\Content\Src\Models\Page::class => 'page',
        \Packages\Content\Src\Models\Post::class => 'post',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Runs AFTER the response is sent to the client (Laravel terminable middleware).
     * Redis round-trips here can't add to perceived latency — fast-cgi_finish_request
     * (when available) detaches the worker before this fires.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldCount($request, $response)) {
            return;
        }

        Report::increment('pageview.total');

        foreach ($request->route()?->parameters() ?? [] as $param) {
            $metricKey = $this->resolveMetricKey($param);
            if ($metricKey !== null) {
                Report::increment($metricKey);
            }
        }
    }

    protected function shouldCount(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Only count successful, non-redirect responses → real eyeball pages.
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            return false;
        }

        $path = trim($request->path(), '/');
        foreach (self::SKIP_PREFIXES as $skip) {
            if ($path === $skip || str_starts_with($path.'/', $skip.'/')) {
                return false;
            }
            // Locale-prefixed equivalents (e.g. vi/admin, en/api) — second segment match.
            if (preg_match('#^[a-z]{2}/'.preg_quote($skip, '#').'(/|$)#', $path) === 1) {
                return false;
            }
        }

        return true;
    }

    protected function resolveMetricKey(mixed $param): ?string
    {
        if (! is_object($param)) {
            return null;
        }

        foreach (self::TRACKABLE as $class => $suffix) {
            if ($param instanceof $class && property_exists($param, 'id') && $param->id !== null) {
                return "pageview.{$suffix}.{$param->id}";
            }
        }

        return null;
    }
}
