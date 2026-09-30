@extends('layouts.app')

@section('title', 'Cloudflare Cache Dashboard')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="page-title mb-1">
            Cloudflare Cache Analytics
        </h2>

        <p class="text-muted mb-0">
            Local cache performance and monitoring dashboard
        </p>
    </div>

    <a
        href="{{ route('cloudflare.monitor') }}"
        class="btn btn-primary"
    >
        🔎 Test URL
    </a>

</div>

<div class="alert alert-info">
    <strong>Local Simulation:</strong>
    This project simulates Cloudflare-style cache behavior locally.
    No external Cloudflare account or domain is required.
</div>

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Total Requests</div>
            <div class="metric-number">
                {{ $analytics['total_requests'] }}
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Cache Hits</div>
            <div class="metric-number text-success">
                {{ $analytics['cache_hits'] }}
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Cache Misses</div>
            <div class="metric-number text-warning">
                {{ $analytics['cache_misses'] }}
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Hit Ratio</div>
            <div class="metric-number text-primary">
                {{ $analytics['cache_hit_ratio'] }}%
            </div>
        </div>
    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Cached Entries</div>
            <div class="metric-number">
                {{ $analytics['cached_entries'] }}
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Bypassed Requests</div>
            <div class="metric-number text-danger">
                {{ $analytics['bypassed_requests'] }}
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Avg Response Time</div>
            <div class="metric-number">
                {{ $analytics['average_response_time'] }} ms
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card dashboard-card p-3">
            <div class="text-muted">Total Purges</div>
            <div class="metric-number">
                {{ $analytics['total_purges'] }}
            </div>
        </div>
    </div>

</div>

<div class="card table-card">

    <div class="card-header bg-white py-3">
        <h5 class="mb-0">
            Recent Cache Requests
        </h5>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>URL</th>
                        <th>Status</th>
                        <th>HTTP</th>
                        <th>Response Time</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($recentRequests as $request)

                    <tr>

                        <td>
                            #{{ $request->id }}
                        </td>

                        <td>
                            <span class="text-break">
                                {{ $request->url }}
                            </span>
                        </td>

                        <td>

                            @if($request->status === 'HIT')
                                <span class="badge bg-success status-badge">
                                    HIT
                                </span>
                            @elseif($request->status === 'MISS')
                                <span class="badge bg-warning text-dark status-badge">
                                    MISS
                                </span>
                            @else
                                <span class="badge bg-danger status-badge">
                                    BYPASS
                                </span>
                            @endif

                        </td>

                        <td>
                            {{ $request->http_status ?? '-' }}
                        </td>

                        <td>
                            {{ $request->response_time ?? 0 }} ms
                        </td>

                        <td>
                            {{ $request->created_at->format('d M Y H:i') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="text-center py-4">
                            No cache requests yet.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection