@extends('layouts.app')

@section('title', 'Cache Management')

@section('content')

<div class="mb-4">

    <h2 class="page-title">
        ⚡ Cache Management
    </h2>

    <p class="text-muted">
        Manage and purge locally simulated cache entries.
    </p>

</div>


<div class="row g-4">

    <div class="col-md-6">

        <div class="card table-card">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    🧹 Purge Specific URL
                </h5>

            </div>

            <div class="card-body">

                <form
                    method="POST"
                    action="{{ route('cloudflare.cache.purge-url') }}"
                >

                    @csrf

                    <div class="mb-3">

                        <label class="form-label">
                            Local URL
                        </label>

                        <input
                            type="url"
                            name="url"
                            class="form-control"
                            placeholder="http://127.0.0.1:8000/"
                            value="{{ old('url') }}"
                            required
                        >

                    </div>

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >
                        🧹 Purge URL Cache
                    </button>

                </form>

            </div>

        </div>

    </div>


    <div class="col-md-6">

        <div class="card table-card">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    🗑️ Purge Everything
                </h5>

            </div>

            <div class="card-body">

                <p>
                    Remove all locally stored cache entries.
                </p>

                <p>

                    Current cached entries:

                    <strong>
                        {{ $analytics['cached_entries'] }}
                    </strong>

                </p>

                <form
                    method="POST"
                    action="{{ route('cloudflare.cache.purge-all') }}"
                    onsubmit="return confirm('Are you sure you want to purge all local cache?')"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        🗑️ Purge All Cache
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<div class="row g-4 mt-1">

    <div class="col-md-3">

        <div class="card dashboard-card p-3">

            <div class="text-muted">
                Active Entries
            </div>

            <div class="metric-number text-success">
                {{ $activeEntries }}
            </div>

        </div>

    </div>


    <div class="col-md-3">

        <div class="card dashboard-card p-3">

            <div class="text-muted">
                Expired Entries
            </div>

            <div class="metric-number text-danger">
                {{ $expiredEntries }}
            </div>

        </div>

    </div>


    <div class="col-md-3">

        <div class="card dashboard-card p-3">

            <div class="text-muted">
                Cache Size
            </div>

            <div class="metric-number">
                {{ number_format($totalCacheSize / 1024, 2) }}
                KB
            </div>

        </div>

    </div>


    <div class="col-md-3">

        <div class="card dashboard-card p-3">

            <div class="text-muted">
                Avg Response
            </div>

            <div class="metric-number">
                {{ $averageEntryResponseTime }}
                ms
            </div>

        </div>

    </div>

</div>


<div class="card table-card mt-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">
            Cache Statistics
        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3">

                <strong>Total Requests</strong>

                <div class="fs-4">
                    {{ $analytics['total_requests'] }}
                </div>

            </div>

            <div class="col-md-3">

                <strong>Cache Hits</strong>

                <div class="fs-4 text-success">
                    {{ $analytics['cache_hits'] }}
                </div>

            </div>

            <div class="col-md-3">

                <strong>Cache Misses</strong>

                <div class="fs-4 text-warning">
                    {{ $analytics['cache_misses'] }}
                </div>

            </div>

            <div class="col-md-3">

                <strong>Hit Ratio</strong>

                <div class="fs-4 text-primary">
                    {{ $analytics['cache_hit_ratio'] }}%
                </div>

            </div>

        </div>

    </div>

</div>

@endsection