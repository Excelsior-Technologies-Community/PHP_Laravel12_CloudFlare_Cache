<?php

namespace App\Http\Controllers;

use App\Models\CacheRequest;
use App\Services\LocalCloudflareCacheService;
use Illuminate\Http\Request;

class CacheMonitorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $requests = CacheRequest::query()
            ->when($search, function ($query) use ($search) {
                $query->where('url', 'like', "%{$search}%");
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('cloudflare.monitor', compact(
            'requests',
            'search',
            'status'
        ));
    }

    public function test(
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
            $result = $cacheService->testUrl($validated['url']);

            return back()
                ->with('test_result', $result)
                ->with('success', 'URL tested successfully.');
        } catch (\Throwable $e) {
            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }
}