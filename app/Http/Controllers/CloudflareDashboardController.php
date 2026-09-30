<?php

namespace App\Http\Controllers;

use App\Models\CachePurge;
use App\Models\CacheRequest;
use App\Models\LocalCacheEntry;
use App\Services\LocalCloudflareCacheService;

class CloudflareDashboardController extends Controller
{
    public function index(LocalCloudflareCacheService $cacheService)
    {
        $analytics = $cacheService->analytics();

        $recentRequests = CacheRequest::oldest()
            ->take(5)
            ->get();

        $recentPurges = CachePurge::oldest()
            ->take(5)
            ->get();

        $activeEntries = LocalCacheEntry::where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        })->count();

        $expiredEntries = LocalCacheEntry::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->count();

        $totalCacheSize = LocalCacheEntry::sum('response_size');

        return view('cloudflare.dashboard', compact(
            'analytics',
            'recentRequests',
            'recentPurges',
            'activeEntries',
            'expiredEntries',
            'totalCacheSize'
        ));
    }
}