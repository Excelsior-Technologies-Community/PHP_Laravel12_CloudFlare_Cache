<?php

namespace App\Http\Controllers;

use App\Models\CachePurge;
use App\Services\LocalCloudflareCacheService;
use Illuminate\Http\Request;

class CloudflareCacheController extends Controller
{
    public function index(LocalCloudflareCacheService $cacheService)
    {
        $analytics = $cacheService->analytics();

        return view('cloudflare.cache', compact('analytics'));
    }

    public function purgeAll(LocalCloudflareCacheService $cacheService)
    {
        try {
            $result = $cacheService->purgeAll();

            return back()->with(
                'success',
                $result['purge']->message
            );
        } catch (\Throwable $e) {
            return back()->with(
                'error',
                'Cache purge failed: ' . $e->getMessage()
            );
        }
    }

    public function purgeUrl(
        Request $request,
        LocalCloudflareCacheService $cacheService
    ) {
        $validated = $request->validate([
            'url' => [
                'required',
                'url',
                'max:2048',
            ],
        ]);

        try {
            $result = $cacheService->purgeUrl($validated['url']);

            return back()->with(
                'success',
                $result['purge']->message
            );
        } catch (\Throwable $e) {
            return back()->with(
                'error',
                'URL cache purge failed: ' . $e->getMessage()
            );
        }
    }

    public function history(Request $request)
    {
        $search = $request->input('search');

        $purges = CachePurge::query()
            ->when($search, function ($query) use ($search) {
                $query->where('type', 'like', "%{$search}%")
                    ->orWhere('target', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('cloudflare.history', compact(
            'purges',
            'search'
        ));
    }
}