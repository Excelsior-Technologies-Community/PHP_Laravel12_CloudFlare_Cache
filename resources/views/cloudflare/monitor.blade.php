@extends('layouts.app')

@section('title', 'URL Cache Monitor')

@section('content')

<div class="mb-4">

    <h2 class="page-title">
        🔎 URL Cache Testing & Monitoring
    </h2>

    <p class="text-muted">
        Test localhost URLs and inspect simulated cache status.
    </p>

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

            <div class="row">

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
                        {{ $result['response_time'] }} ms
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
                    {{ $result['cache_age'] }} seconds
                </div>

                <div class="col-md-4">
                    <strong>Request ID:</strong>
                    #{{ $result['request_id'] }}
                </div>

            </div>

            <div class="alert alert-info mt-4 mb-0">
                {{ $result['message'] }}
            </div>

        </div>

    </div>

@endif

<div class="card table-card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Request History
            </h5>

            <span class="badge bg-secondary">
                {{ $requests->total() }} Requests
            </span>

        </div>

    </div>

    <div class="card-body">

        <form method="GET" class="row g-2 mb-4">

            <div class="col-md-7">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search URL..."
                    value="{{ $search }}"
                >

            </div>

            <div class="col-md-3">

                <select name="status" class="form-select">

                    <option value="">
                        All Statuses
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

                <button class="btn btn-dark w-100">
                    Filter
                </button>

            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>URL</th>
                        <th>Status</th>
                        <th>HTTP</th>
                        <th>Response</th>
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
                            {{ $request->created_at->format('d M Y H:i') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="text-center py-4">
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