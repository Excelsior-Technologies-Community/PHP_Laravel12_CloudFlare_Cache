@extends('layouts.app')

@section('title', 'Cache Purge History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="page-title">
            🧹 Cache Purge History
        </h2>

        <p class="text-muted mb-0">
            Search, filter, sort and export cache purge operations.
        </p>

    </div>

    <a
        href="{{ route('cloudflare.cache.history.export', request()->query()) }}"
        class="btn btn-success"
    >
        📥 Export CSV
    </a>

</div>


<div class="card filter-card mb-4">

    <div class="card-body">

        <form
            method="GET"
            action="{{ route('cloudflare.cache.history') }}"
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
                        placeholder="Type, target, status or message..."
                        value="{{ $search }}"
                    >

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Type
                    </label>

                    <select
                        name="type"
                        class="form-select"
                    >

                        <option value="">
                            All Types
                        </option>

                        <option
                            value="URL"
                            {{ $type === 'URL' ? 'selected' : '' }}
                        >
                            URL
                        </option>

                        <option
                            value="ALL"
                            {{ $type === 'ALL' ? 'selected' : '' }}
                        >
                            ALL
                        </option>

                    </select>

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
                            All Status
                        </option>

                        <option
                            value="SUCCESS"
                            {{ $status === 'SUCCESS' ? 'selected' : '' }}
                        >
                            SUCCESS
                        </option>

                        <option
                            value="FAILED"
                            {{ $status === 'FAILED' ? 'selected' : '' }}
                        >
                            FAILED
                        </option>

                    </select>

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
                            value="target"
                            {{ $sort === 'target' ? 'selected' : '' }}
                        >
                            Target
                        </option>

                    </select>

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        &nbsp;
                    </label>

                    <button class="btn btn-dark w-100">
                        Filter
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>


<div class="card table-card">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover">

                <thead class="table-light">

                    <tr>

                        <th>ID</th>
                        <th>Type</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Date</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($purges as $purge)

                    <tr>

                        <td>
                            #{{ $purge->id }}
                        </td>

                        <td>

                            @if($purge->type === 'ALL')

                                <span class="badge bg-danger">
                                    ALL
                                </span>

                            @else

                                <span class="badge bg-warning text-dark">
                                    URL
                                </span>

                            @endif

                        </td>

                        <td class="text-break">
                            {{ $purge->target ?? 'All Cache Entries' }}
                        </td>

                        <td>

                            @if($purge->status === 'SUCCESS')

                                <span class="badge bg-success">
                                    SUCCESS
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    FAILED
                                </span>

                            @endif

                        </td>

                        <td>
                            {{ $purge->message }}
                        </td>

                        <td>
                            {{ $purge->created_at?->format('d M Y H:i') }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="text-center py-4"
                        >
                            No purge history available.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $purges->links() }}
        </div>

    </div>

</div>

@endsection