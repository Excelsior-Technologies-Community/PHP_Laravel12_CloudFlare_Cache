@extends('layouts.app')

@section('title', 'Cache Entries')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="page-title">
            📦 Local Cache Entries
        </h2>

        <p class="text-muted mb-0">
            Search, inspect and remove locally stored cache entries.
        </p>

    </div>

</div>


<div class="card filter-card mb-4">

    <div class="card-body">

        <form
            method="GET"
            action="{{ route('cloudflare.cache.entries') }}"
        >

            <div class="row g-3">

                <div class="col-md-6">

                    <label class="form-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cache key, URL or content type..."
                        value="{{ $search }}"
                    >

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Expiry
                    </label>

                    <select
                        name="expiry"
                        class="form-select"
                    >

                        <option
                            value="all"
                            {{ $expiry === 'all' ? 'selected' : '' }}
                        >
                            All Entries
                        </option>

                        <option
                            value="active"
                            {{ $expiry === 'active' ? 'selected' : '' }}
                        >
                            Active
                        </option>

                        <option
                            value="expired"
                            {{ $expiry === 'expired' ? 'selected' : '' }}
                        >
                            Expired
                        </option>

                    </select>

                </div>

                <div class="col-md-3">

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


<form
    method="POST"
    action="{{ route('cloudflare.cache.entries.delete') }}"
    onsubmit="return confirm('Delete all selected cache entries?')"
>

    @csrf

    @method('DELETE')


    <div class="card table-card">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    Cache Entries
                </h5>

                <button
                    type="submit"
                    class="btn btn-danger btn-sm"
                >
                    🗑️ Delete Selected
                </button>

            </div>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead class="table-light">

                        <tr>

                            <th>
                                <input
                                    type="checkbox"
                                    id="selectAll"
                                    class="form-check-input"
                                >
                            </th>

                            <th>ID</th>
                            <th>Cache Key</th>
                            <th>URL</th>
                            <th>HTTP</th>
                            <th>Response</th>
                            <th>Size</th>
                            <th>Expiry</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($entries as $entry)

                        <tr>

                            <td>

                                <input
                                    type="checkbox"
                                    name="entry_ids[]"
                                    value="{{ $entry->id }}"
                                    class="form-check-input entry-checkbox"
                                >

                            </td>

                            <td>
                                #{{ $entry->id }}
                            </td>

                            <td class="text-break">
                                {{ $entry->cache_key }}
                            </td>

                            <td class="text-break">
                                {{ $entry->url }}
                            </td>

                            <td>
                                {{ $entry->http_status ?? '-' }}
                            </td>

                            <td>
                                {{ $entry->response_time ?? 0 }} ms
                            </td>

                            <td>
                                {{ number_format($entry->response_size ?? 0) }}
                                bytes
                            </td>

                            <td>

                                @if(
                                    $entry->expires_at &&
                                    $entry->expires_at->lte(now())
                                )

                                    <span class="badge bg-danger">
                                        EXPIRED
                                    </span>

                                    <div class="small text-muted mt-1">
                                        {{ $entry->expires_at->format('d M Y H:i') }}
                                    </div>

                                @else

                                    <span class="badge bg-success">
                                        ACTIVE
                                    </span>

                                    @if($entry->expires_at)

                                        <div class="small text-muted mt-1">
                                            {{ $entry->expires_at->format('d M Y H:i') }}
                                        </div>

                                    @endif

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-4"
                            >
                                No cache entries found.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $entries->links() }}
            </div>

        </div>

    </div>

</form>


@push('scripts')

<script>

document.getElementById('selectAll')?.addEventListener(
    'change',
    function () {

        document
            .querySelectorAll('.entry-checkbox')
            .forEach(function (checkbox) {

                checkbox.checked = this.checked;

            }, this);

    }
);

</script>

@endpush

@endsection