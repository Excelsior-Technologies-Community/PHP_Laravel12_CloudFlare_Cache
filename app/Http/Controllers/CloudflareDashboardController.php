<?php

namespace App\Http\Controllers;

use App\Models\CachePurge;
use App\Models\CacheRequest;
use App\Services\LocalCloudflareCacheService;

class CloudflareDashboardController extends Controller
{
    public function index(LocalCloudflareCacheService $cacheService)
    {
        $analytics = $cacheService->analytics();

        $recentRequests = CacheRequest::latest()
            ->take(10)
            ->get();

        $recentPurges = CachePurge::latest()
            ->take(5)
            ->get();

        return view('cloudflare.dashboard', compact(
            'analytics',
            'recentRequests',
            'recentPurges'
        ));
    }
}