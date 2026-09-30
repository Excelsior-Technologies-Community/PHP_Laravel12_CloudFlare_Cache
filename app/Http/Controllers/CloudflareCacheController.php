<?php

namespace App\Http\Controllers;

use App\Models\CachePurge;
use App\Models\LocalCacheEntry;
use App\Services\LocalCloudflareCacheService;
use Illuminate\Http\Request;

class CloudflareCacheController extends Controller
{
    public function index(LocalCloudflareCacheService $cacheService)
    {
        $analytics = $cacheService->analytics();

        $activeEntries = LocalCacheEntry::where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        })->count();

        $expiredEntries = LocalCacheEntry::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->count();

        $totalCacheSize = LocalCacheEntry::sum('response_size');

        $averageEntryResponseTime = round(
            (float) LocalCacheEntry::avg('response_time'),
            2
        );

        return view('cloudflare.cache', compact(
            'analytics',
            'activeEntries',
            'expiredEntries',
            'totalCacheSize',
            'averageEntryResponseTime'
        ));
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
        $status = $request->input('status');
        $type = $request->input('type');
        $sort = $request->input('sort', 'newest');

        $query = CachePurge::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%")
                        ->orWhere('target', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            });

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'target':
                $query->orderBy('target');
                break;

            default:
                $query->oldest();
                break;
        }

        $purges = $query
            ->paginate(5)
            ->withQueryString();

        return view('cloudflare.history', compact(
            'purges',
            'search',
            'status',
            'type',
            'sort'
        ));
    }

    public function exportHistory(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');
        $sort = $request->input('sort', 'newest');

        $query = CachePurge::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%")
                        ->orWhere('target', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            });

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'target':
                $query->orderBy('target');
                break;

            default:
                $query->oldest();
                break;
        }

        $purges = $query->get();

        return response()->streamDownload(function () use ($purges) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Type',
                'Target',
                'Status',
                'Message',
                'Created At',
            ]);

            foreach ($purges as $purge) {
                fputcsv($handle, [
                    $purge->id,
                    $purge->type,
                    $purge->target,
                    $purge->status,
                    $purge->message,
                    $purge->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 'cache-purge-history.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function entries(Request $request)
    {
        $search = $request->input('search');
        $expiry = $request->input('expiry', 'all');

        $query = LocalCacheEntry::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('cache_key', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('content_type', 'like', "%{$search}%");
                });
            });

        if ($expiry === 'active') {
            $query->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
        }

        if ($expiry === 'expired') {
            $query->whereNotNull('expires_at')
                ->where('expires_at', '<=', now());
        }

        $entries = $query
            ->oldest()
            ->paginate(5)
            ->withQueryString();

        return view('cloudflare.entries', compact(
            'entries',
            'search',
            'expiry'
        ));
    }

    public function deleteEntries(Request $request)
    {
        $validated = $request->validate([
            'entry_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'entry_ids.*' => [
                'integer',
                'exists:local_cache_entries,id',
            ],
        ]);

        $deleted = LocalCacheEntry::whereIn(
            'id',
            $validated['entry_ids']
        )->delete();

        return back()->with(
            'success',
            "{$deleted} cache entr" .
            ($deleted === 1 ? 'y was' : 'ies were') .
            " deleted successfully."
        );
    }
}