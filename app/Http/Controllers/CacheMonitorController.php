<?php

namespace App\Http\Controllers;

use App\Models\CacheRequest;
use App\Services\LocalCloudflareCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CacheMonitorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $sort = $request->input('sort', 'newest');
        $maxResponseTime = $request->input('max_response_time');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = CacheRequest::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('url', 'like', "%{$search}%")
                        ->orWhere('method', 'like', "%{$search}%")
                        ->orWhere('content_type', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($maxResponseTime !== null && $maxResponseTime !== '', function ($query) use ($maxResponseTime) {
                $query->where('response_time', '<=', (float) $maxResponseTime);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            });

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'fastest':
                $query->orderBy('response_time', 'asc');
                break;

            case 'slowest':
                $query->orderBy('response_time', 'desc');
                break;

            case 'url':
                $query->orderBy('url', 'asc');
                break;

            default:
                $query->latest();
                break;
        }

        $requests = $query
            ->paginate(5)
            ->withQueryString();

        return view('cloudflare.monitor', compact(
            'requests',
            'search',
            'status',
            'sort',
            'maxResponseTime',
            'dateFrom',
            'dateTo'
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

    public function export(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $sort = $request->input('sort', 'newest');
        $maxResponseTime = $request->input('max_response_time');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = CacheRequest::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('url', 'like', "%{$search}%")
                        ->orWhere('method', 'like', "%{$search}%")
                        ->orWhere('content_type', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($maxResponseTime !== null && $maxResponseTime !== '', function ($query) use ($maxResponseTime) {
                $query->where('response_time', '<=', (float) $maxResponseTime);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            });

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'fastest':
                $query->orderBy('response_time', 'asc');
                break;

            case 'slowest':
                $query->orderBy('response_time', 'desc');
                break;

            case 'url':
                $query->orderBy('url', 'asc');
                break;

            default:
                $query->latest();
                break;
        }

        $requests = $query->get();

        return response()->streamDownload(function () use ($requests) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'URL',
                'Method',
                'Cache Status',
                'HTTP Status',
                'Response Time (ms)',
                'Response Size',
                'Content Type',
                'Created At',
            ]);

            foreach ($requests as $request) {
                fputcsv($handle, [
                    $request->id,
                    $request->url,
                    $request->method,
                    $request->status,
                    $request->http_status,
                    $request->response_time,
                    $request->response_size,
                    $request->content_type,
                    $request->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 'cache-request-history.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}