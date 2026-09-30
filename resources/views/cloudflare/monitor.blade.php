@extends('layouts.app')

@section('title', 'URL Cache Monitor')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="page-title">
            🔎 URL Cache Testing & Monitoring
        </h2>

        <p class="text-muted mb-0">
            Test URLs and analyze simulated cache performance.
        </p>

    </div>

    <a
        href="{{ route('cloudflare.monitor.export', request()->query()) }}"
        class="btn btn-success"
    >
        📥 Export CSV
    </a>

</div>


<div class="card table-card mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">
            Test Local URL
        </h5>

    </div>

    <div class="card-body">

        <form
            method="POST"
            action="{{ route('cloudflare.monitor.test') }}"
        >

            @csrf

            <div class="row g-2">

                <div class="col-md-10">

                    <input
                        type="url"
                        name="url"
                        class="form-control"
                        placeholder="http://127.0.0.1:8000/"
                        value="{{ old('url', 'http://127.0.0.1:8000/') }}"
                        required
                    >

                </div>

                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Test URL
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>


@if(session('test_result'))

    @php
        $result = session('test_result');
    @endphp

    <div class="card table-card mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Test Result
            </h5>

        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-3">

                    <strong>Cache Status</strong>

                    <div class="mt-2">

                        @if($result['cache_status'] === 'HIT')

                            <span class="badge bg-success fs-6">
                                HIT
                            </span>

                        @elseif($result['cache_status'] === 'MISS')

                            <span class="badge bg-warning text-dark fs-6">
                                MISS
                            </span>

                        @else

                            <span class="badge bg-danger fs-6">
                                BYPASS
                            </span>

                        @endif

                    </div>

                </div>

                <div class="col-md-3">

                    <strong>HTTP Status</strong>

                    <div class="fs-5 mt-2">
                        {{ $result['http_status'] ?? '-' }}
                    </div>

                </div>

                <div class="col-md-3">

                    <strong>Response Time</strong>

                    <div class="fs-5 mt-2">
                        {{ $result['response_time'] ?? 0 }} ms
                    </div>

                </div>

                <div class="col-md-3">

                    <strong>Response Size</strong>

                    <div class="fs-5 mt-2">
                        {{ number_format($result['response_size'] ?? 0) }}
                        bytes
                    </div>

                </div>

            </div>

            <hr>

            <div class="row">

                <div class="col-md-4">

                    <strong>Content Type:</strong>

                    {{ $result['content_type'] ?? '-' }}

                </div>

                <div class="col-md-4">

                    <strong>Cache Age:</strong>

                    {{ $result['cache_age'] ?? 0 }}
                    seconds

                </div>

                <div class="col-md-4">

                    <strong>Request ID:</strong>

                    #{{ $result['request_id'] ?? '-' }}

                </div>

            </div>

            <div class="alert alert-info mt-4 mb-0">

                {{ $result['message'] ?? 'Test completed.' }}

            </div>

        </div>

    </div>

@endif


<div class="card filter-card mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">
            🔍 Advanced Request Filters
        </h5>

    </div>

    <div class="card-body">

        <form
            method="GET"
            action="{{ route('cloudflare.monitor') }}"
        >

            <div class="row g-3">

                <div class="col-md-4">

                    <label class="form-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="URL, method or content type..."
                        value="{{ $search }}"
                    >

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option value="">
                            All
                        </option>

                        <option
                            value="HIT"
                            {{ $status === 'HIT' ? 'selected' : '' }}
                        >
                            HIT
                        </option>

                        <option
                            value="MISS"
                            {{ $status === 'MISS' ? 'selected' : '' }}
                        >
                            MISS
                        </option>

                        <option
                            value="BYPASS"
                            {{ $status === 'BYPASS' ? 'selected' : '' }}
                        >
                            BYPASS
                        </option>

                    </select>

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Max Response (ms)
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="max_response_time"
                        class="form-control"
                        value="{{ $maxResponseTime }}"
                    >

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Sort
                    </label>

                    <select
                        name="sort"
                        class="form-select"
                    >

                        <option
                            value="newest"
                            {{ $sort === 'newest' ? 'selected' : '' }}
                        >
                            Newest
                        </option>

                        <option
                            value="oldest"
                            {{ $sort === 'oldest' ? 'selected' : '' }}
                        >
                            Oldest
                        </option>

                        <option
                            value="fastest"
                            {{ $sort === 'fastest' ? 'selected' : '' }}
                        >
                            Fastest
                        </option>

                        <option
                            value="slowest"
                            {{ $sort === 'slowest' ? 'selected' : '' }}
                        >
                            Slowest
                        </option>

                        <option
                            value="url"
                            {{ $sort === 'url' ? 'selected' : '' }}
                        >
                            URL
                        </option>

                    </select>

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        &nbsp;
                    </label>

                    <button
                        class="btn btn-dark w-100"
                    >
                        Apply
                    </button>

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        From Date
                    </label>

                    <input
                        type="date"
                        name="date_from"
                        class="form-control"
                        value="{{ $dateFrom }}"
                    >

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        To Date
                    </label>

                    <input
                        type="date"
                        name="date_to"
                        class="form-control"
                        value="{{ $dateTo }}"
                    >

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        &nbsp;
                    </label>

                    <a
                        href="{{ route('cloudflare.monitor') }}"
                        class="btn btn-outline-secondary w-100"
                    >
                        Reset Filters
                    </a>

                </div>

            </div>

        </form>

    </div>

</div>


<div class="card table-card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between">

            <h5 class="mb-0">
                Request History
            </h5>

            <span class="badge bg-secondary">
                {{ $requests->total() }} Requests
            </span>

        </div>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>URL</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>HTTP</th>
                        <th>Response</th>
                        <th>Size</th>
                        <th>Date</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($requests as $request)

                    <tr>

                        <td>
                            #{{ $request->id }}
                        </td>

                        <td class="text-break">
                            {{ $request->url }}
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                {{ $request->method }}
                            </span>
                        </td>

                        <td>

                            @if($request->status === 'HIT')

                                <span class="badge bg-success">
                                    HIT
                                </span>

                            @elseif($request->status === 'MISS')

                                <span class="badge bg-warning text-dark">
                                    MISS
                                </span>

                            @else

                                <span class="badge bg-danger">
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
                            {{ number_format($request->response_size ?? 0) }}
                        </td>

                        <td>
                            {{ $request->created_at?->format('d M Y H:i') }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="8"
                            class="text-center py-4"
                        >
                            No requests found.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $requests->links() }}
        </div>

    </div>

</div>

@endsection