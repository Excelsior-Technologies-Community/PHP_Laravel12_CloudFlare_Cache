<?php

namespace App\Services;

use App\Models\CachePurge;
use App\Models\CacheRequest;
use App\Models\LocalCacheEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class LocalCloudflareCacheService
{
    /**
     * Test a local Laravel URL and simulate Cloudflare-style cache behavior.
     */
    public function testUrl(string $url): array
    {
        if (!$this->isAllowedUrl($url)) {
            throw new \InvalidArgumentException(
                'Only localhost URLs are allowed. Use http://127.0.0.1:8000 or http://localhost.'
            );
        }

        $cacheKey = hash('sha256', $url);

        /*
         * Check whether a valid cached response already exists.
         */
        $existingCache = LocalCacheEntry::where('cache_key', $cacheKey)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        /*
         * CACHE HIT
         */
        if ($existingCache) {
            $cacheRequest = CacheRequest::create([
                'url' => $url,
                'method' => 'GET',
                'status' => 'HIT',
                'http_status' => $existingCache->http_status,
                'response_time' => 1,
                'response_size' => $existingCache->response_size,
                'content_type' => $existingCache->content_type,
            ]);

            return [
                'success' => true,
                'cache_status' => 'HIT',
                'http_status' => $existingCache->http_status,
                'response_time' => 1,
                'response_size' => $existingCache->response_size,
                'content_type' => $existingCache->content_type,
                'cache_age' => $existingCache->created_at
                    ? $existingCache->created_at->diffInSeconds(now())
                    : 0,
                'message' => 'Response served from the local cache.',
                'request_id' => $cacheRequest->id,
            ];
        }

        /*
         * CACHE MISS
         *
         * Instead of making a network request back to
         * 127.0.0.1:8000, dispatch the Laravel route internally.
         *
         * This avoids the localhost self-request timeout.
         */
        $start = microtime(true);

        try {
            $parsedUrl = parse_url($url);

            $path = $parsedUrl['path'] ?? '/';

            if (!empty($parsedUrl['query'])) {
                $path .= '?' . $parsedUrl['query'];
            }

            $request = Request::create(
                $path,
                'GET',
                [],
                [],
                [],
                [
                    'HTTP_HOST' => $parsedUrl['host'],
                    'SERVER_NAME' => $parsedUrl['host'],
                    'SERVER_PORT' => $parsedUrl['port'] ?? 8000,
                    'REQUEST_SCHEME' => $parsedUrl['scheme'] ?? 'http',
                    'HTTPS' => ($parsedUrl['scheme'] ?? 'http') === 'https'
                        ? 'on'
                        : 'off',
                ]
            );

            /*
             * Dispatch the request directly through Laravel's router.
             */
            $response = app('router')->dispatch($request);

            $responseTime = (int) round(
                (microtime(true) - $start) * 1000
            );

            $body = $response->getContent();

            $responseSize = strlen($body);

            $contentType = $response->headers->get(
                'Content-Type',
                'text/html'
            );

            $httpStatus = $response->getStatusCode();

            /*
             * Store the response information in our local cache.
             */
            LocalCacheEntry::updateOrCreate(
                [
                    'cache_key' => $cacheKey,
                ],
                [
                    'url' => $url,
                    'http_status' => $httpStatus,
                    'response_time' => $responseTime,
                    'response_size' => $responseSize,
                    'content_type' => $contentType,
                    'expires_at' => now()->addMinutes(30),
                ]
            );

            /*
             * Store request analytics.
             */
            $cacheRequest = CacheRequest::create([
                'url' => $url,
                'method' => 'GET',
                'status' => 'MISS',
                'http_status' => $httpStatus,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'content_type' => $contentType,
            ]);

            return [
                'success' => true,
                'cache_status' => 'MISS',
                'http_status' => $httpStatus,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'content_type' => $contentType,
                'cache_age' => 0,
                'message' => 'Response fetched from the local Laravel application and stored in the simulated cache.',
                'request_id' => $cacheRequest->id,
            ];

        } catch (\Throwable $e) {

            $responseTime = (int) round(
                (microtime(true) - $start) * 1000
            );

            CacheRequest::create([
                'url' => $url,
                'method' => 'GET',
                'status' => 'BYPASS',
                'http_status' => null,
                'response_time' => $responseTime,
                'response_size' => 0,
                'content_type' => null,
            ]);

            throw new \RuntimeException(
                'Unable to process the local Laravel URL: '
                . $e->getMessage()
            );
        }
    }

    /**
     * Purge one locally cached URL.
     */
    public function purgeUrl(string $url): array
    {
        if (!$this->isAllowedUrl($url)) {
            throw new \InvalidArgumentException(
                'Only localhost URLs are allowed.'
            );
        }

        $cacheKey = hash('sha256', $url);

        $deleted = LocalCacheEntry::where(
            'cache_key',
            $cacheKey
        )->delete();

        $purge = CachePurge::create([
            'type' => 'URL',
            'target' => $url,
            'status' => 'SUCCESS',
            'message' => $deleted
                ? 'URL cache entry successfully purged.'
                : 'No cache entry existed for this URL.',
        ]);

        return [
            'success' => true,
            'purge' => $purge,
            'deleted' => $deleted,
        ];
    }

    /**
     * Purge all locally cached entries.
     */
    public function purgeAll(): array
    {
        $count = LocalCacheEntry::count();

        LocalCacheEntry::query()->delete();

        $purge = CachePurge::create([
            'type' => 'ALL',
            'target' => null,
            'status' => 'SUCCESS',
            'message' => $count
                . ' cache entries were purged successfully.',
        ]);

        return [
            'success' => true,
            'purge' => $purge,
            'deleted' => $count,
        ];
    }

    /**
     * Allow only localhost URLs.
     */
    public function isAllowedUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (!$parsed || empty($parsed['host'])) {
            return false;
        }

        $host = strtolower($parsed['host']);

        return in_array($host, [
            '127.0.0.1',
            'localhost',
            '::1',
        ], true);
    }

    /**
     * Generate cache analytics.
     */
    public function analytics(): array
    {
        $totalRequests = CacheRequest::count();

        $cacheHits = CacheRequest::where(
            'status',
            'HIT'
        )->count();

        $cacheMisses = CacheRequest::where(
            'status',
            'MISS'
        )->count();

        $bypassedRequests = CacheRequest::where(
            'status',
            'BYPASS'
        )->count();

        $cacheHitRatio = $totalRequests > 0
            ? round(($cacheHits / $totalRequests) * 100, 2)
            : 0;

        $totalBytes = (int) CacheRequest::sum(
            'response_size'
        );

        $averageResponseTime = round(
            (float) CacheRequest::whereNotNull(
                'response_time'
            )->avg('response_time'),
            2
        );

        $cachedBytes = (int) CacheRequest::where(
            'status',
            'HIT'
        )->sum('response_size');

        return [
            'total_requests' => $totalRequests,
            'cache_hits' => $cacheHits,
            'cache_misses' => $cacheMisses,
            'bypassed_requests' => $bypassedRequests,
            'cache_hit_ratio' => $cacheHitRatio,
            'total_bytes' => $totalBytes,
            'cached_bytes' => $cachedBytes,
            'average_response_time' => $averageResponseTime,
            'cached_entries' => LocalCacheEntry::count(),
            'total_purges' => CachePurge::count(),
        ];
    }
}