<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * Threshold for slow queries in milliseconds.
     */
    protected int $slowQueryThreshold = 1000;

    /**
     * Threshold for slow requests in milliseconds.
     */
    protected int $slowRequestThreshold = 2000;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start timing the request
        $startTime = microtime(true);
        $queryCount = 0;
        $slowQueries = [];

        // Enable query logging
        DB::enableQueryLog();

        // Listen for database queries
        DB::listen(function ($query) use (&$queryCount, &$slowQueries) {
            $queryCount++;

            // Log slow queries
            if ($query->time > $this->slowQueryThreshold) {
                $slowQueries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ];

                Log::channel('performance')->warning('Slow database query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time.'ms',
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                ]);
            }
        });

        $response = $next($request);

        // Calculate request duration
        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        // Log slow requests
        if ($duration > $this->slowRequestThreshold) {
            Log::channel('performance')->warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration' => round($duration, 2).'ms',
                'query_count' => $queryCount,
                'slow_queries_count' => count($slowQueries),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);
        }

        // Add performance headers (only in development)
        if (app()->environment('local')) {
            $response->headers->set('X-Request-Duration', round($duration, 2).'ms');
            $response->headers->set('X-Query-Count', $queryCount);
            $response->headers->set('X-Slow-Queries', count($slowQueries));
        }

        return $response;
    }
}
