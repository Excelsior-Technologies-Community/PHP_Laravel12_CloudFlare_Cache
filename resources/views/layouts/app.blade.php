<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Cloudflare Cache Manager')
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f7fb;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .dashboard-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
        }

        .metric-number {
            font-size: 28px;
            font-weight: 700;
        }

        .page-title {
            font-weight: 700;
        }

        .table-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
        }

        .status-badge {
            min-width: 70px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand"
           href="{{ route('cloudflare.dashboard') }}">
            ☁️ Cloudflare Cache Manager
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="{{ route('cloudflare.dashboard') }}"
                    >
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="{{ route('cloudflare.cache') }}"
                    >
                        Cache Management
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="{{ route('cloudflare.monitor') }}"
                    >
                        URL Monitor
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="{{ route('cloudflare.cache.history') }}"
                    >
                        Purge History
                    </a>
                </li>

            </ul>

        </div>
    </div>
</nav>

<main class="container py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    @yield('content')

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>