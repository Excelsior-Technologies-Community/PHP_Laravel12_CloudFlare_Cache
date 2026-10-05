# PHP_Laravel12_CloudFlare_Cache

## 1. Introduction

This project provides a **local Cloudflare-style Cache Manager** built with Laravel 12.

It simulates Cloudflare cache functionality locally without requiring a real Cloudflare domain, Cloudflare API token, or external Cloudflare account.

The project allows users to monitor cache performance, test localhost URLs, purge cached URLs, and maintain purge history.

**Key Features:**

- 📊 Cloudflare Cache Analytics & Performance Dashboard.
- ⚡ Cache Purge Management & History.
- 🔎 URL Cache Testing & Status Monitoring.
- Track total requests, cache hits, misses, and bypassed requests.
- Calculate cache hit ratio.
- Store locally simulated cache entries.
- Purge a specific URL from the cache.
- Purge all cached entries.
- Maintain persistent cache purge history.
- Test localhost URLs and detect `HIT`, `MISS`, and `BYPASS`.
- Monitor HTTP status, response time, response size, and content type.
- Search and filter cache request history.
- Search and paginate purge history.
- Bootstrap 5 responsive dashboard.
- No Cloudflare API configuration required.

> **Note:** This project is a local Cloudflare cache simulation created for development, learning, and demonstration purposes.

---

# 2. Project Setup

## Step 1: Create Laravel 12 Project

Create a new Laravel 12 project:

```bash
composer create-project laravel/laravel PHP_Laravel12_CloudFlare_Cache "12.*"
cd PHP_Laravel12_CloudFlare_Cache
```

---

## Step 2: Setup Environment

Copy the example environment file:

```bash
copy .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

---

## Step 3: Configure Database

Open the `.env` file and configure MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloudflare_cache
DB_USERNAME=root
DB_PASSWORD=
```

Create the database in MySQL/phpMyAdmin:

```text
cloudflare_cache
```

Make sure MySQL is running in XAMPP.

---

## Step 4: Install Laravel Dependencies

Run:

```bash
composer install
```

Clear Laravel caches:

```bash
php artisan optimize:clear
```

---

## Step 5: Database Migrations

This project uses three database tables:

1. `cache_requests`
2. `local_cache_entries`
3. `cache_purges`

Create the migrations:

```bash
php artisan make:migration create_cache_requests_table
php artisan make:migration create_local_cache_entries_table
php artisan make:migration create_cache_purges_table
```

---

# 3. Cache Requests Migration

## Step 1: Migration File

File location:

```text
database/migrations/xxxx_xx_xx_xxxxxx_create_cache_requests_table.php
```

## Step 2: Migration File Content

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache_requests', function (Blueprint $table) {
            $table->id();

            // Requested localhost URL
            $table->string('url');

            // HTTP request method
            $table->string('method')->default('GET');

            // Cache status: HIT, MISS, or BYPASS
            $table->string('status')->default('MISS');

            // HTTP response status code
            $table->unsignedSmallInteger('http_status')->nullable();

            // Response processing time in milliseconds
            $table->unsignedInteger('response_time')->nullable();

            // Response size in bytes
            $table->unsignedBigInteger('response_size')->nullable();

            // Response content type
            $table->string('content_type')->nullable();

            $table->timestamps();

            // Indexes for faster filtering
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_requests');
    }
};
```

---

# 4. Local Cache Entries Migration

## Step 1: Migration File

File location:

```text
database/migrations/xxxx_xx_xx_xxxxxx_create_local_cache_entries_table.php
```

## Step 2: Migration File Content

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('local_cache_entries', function (Blueprint $table) {
            $table->id();

            // Unique hash used to identify cached URLs
            $table->string('cache_key')->unique();

            // Original URL
            $table->text('url');

            // HTTP response status
            $table->unsignedSmallInteger('http_status')->nullable();

            // Original response time
            $table->unsignedInteger('response_time')->nullable();

            // Response size in bytes
            $table->unsignedBigInteger('response_size')->nullable();

            // Response content type
            $table->string('content_type')->nullable();

            // Cache expiration time
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Index for URL lookup
            $table->index('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_cache_entries');
    }
};
```

---

# 5. Cache Purges Migration

## Step 1: Migration File

File location:

```text
database/migrations/xxxx_xx_xx_xxxxxx_create_cache_purges_table.php
```

## Step 2: Migration File Content

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache_purges', function (Blueprint $table) {
            $table->id();

            // Purge type: URL or ALL
            $table->string('type');

            // Purged URL
            $table->text('target')->nullable();

            // Purge operation status
            $table->string('status');

            // Purge result message
            $table->string('message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_purges');
    }
};
```

---

## Step 3: Run Migrations

Run:

```bash
php artisan migrate
```

This will create:

```text
cache_requests
local_cache_entries
cache_purges
```

tables in the configured database.

---

# 6. Models

## Step 1: Create CacheRequest Model

```bash
php artisan make:model CacheRequest
```

File:

```text
app/Models/CacheRequest.php
```

Content:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CacheRequest extends Model
{
    protected $fillable = [
        'url',
        'method',
        'status',
        'http_status',
        'response_time',
        'response_size',
        'content_type',
    ];
}
```

---

## Step 2: Create LocalCacheEntry Model

```bash
php artisan make:model LocalCacheEntry
```

File:

```text
app/Models/LocalCacheEntry.php
```

Content:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalCacheEntry extends Model
{
    protected $fillable = [
        'cache_key',
        'url',
        'http_status',
        'response_time',
        'response_size',
        'content_type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
```

---

## Step 3: Create CachePurge Model

```bash
php artisan make:model CachePurge
```

File:

```text
app/Models/CachePurge.php
```

Content:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CachePurge extends Model
{
    protected $fillable = [
        'type',
        'target',
        'status',
        'message',
    ];
}
```

---

# 7. Local Cloudflare Cache Service

## Step 1: Create Service Directory

Create:

```text
app/Services/
```

Then create:

```text
app/Services/LocalCloudflareCacheService.php
```

## Step 2: Service File

```php
<?php

namespace App\Services;

use App\Models\CachePurge;
use App\Models\CacheRequest;
use App\Models\LocalCacheEntry;
use Illuminate\Http\Request;

class LocalCloudflareCacheService
{
    /**
     * Test a localhost URL using the local cache simulation.
     */
    public function testUrl(string $url): array
    {
        if (!$this->isAllowedUrl($url)) {
            throw new \InvalidArgumentException(
                'Only localhost URLs are allowed. Use http://127.0.0.1:8000 or http://localhost.'
            );
        }

        // Generate a unique cache key from the URL.
        $cacheKey = hash('sha256', $url);

        // Check whether a valid cache entry already exists.
        $existingCache = LocalCacheEntry::where(
            'cache_key',
            $cacheKey
        )
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        /*
         * CACHE HIT
         */
        if ($existingCache) {
            $cacheRequest = CacheRequest::create([
                'url' => $url,
                'method' => 'GET',
                'status' => 'HIT',
                'http_status' => $existingCache->http_status,
                'response_time' => 1,
                'response_size' => $existingCache->response_size,
                'content_type' => $existingCache->content_type,
            ]);

            return [
                'success' => true,
                'cache_status' => 'HIT',
                'http_status' => $existingCache->http_status,
                'response_time' => 1,
                'response_size' => $existingCache->response_size,
                'content_type' => $existingCache->content_type,
                'cache_age' => $existingCache->created_at
                    ? $existingCache->created_at->diffInSeconds(now())
                    : 0,
                'message' => 'Response served from the local cache.',
                'request_id' => $cacheRequest->id,
            ];
        }

        $start = microtime(true);

        try {
            $parsedUrl = parse_url($url);

            $path = $parsedUrl['path'] ?? '/';

            if (!empty($parsedUrl['query'])) {
                $path .= '?' . $parsedUrl['query'];
            }

            /*
             * Dispatch the request internally through Laravel.
             *
             * This avoids making an HTTP request back to the same
             * php artisan serve process.
             */
            $request = Request::create(
                $path,
                'GET',
                [],
                [],
                [],
                [
                    'HTTP_HOST' => $parsedUrl['host'],
                    'SERVER_NAME' => $parsedUrl['host'],
                    'SERVER_PORT' => $parsedUrl['port'] ?? 8000,
                    'REQUEST_SCHEME' => $parsedUrl['scheme'] ?? 'http',
                    'HTTPS' => ($parsedUrl['scheme'] ?? 'http') === 'https'
                        ? 'on'
                        : 'off',
                ]
            );

            $response = app('router')->dispatch($request);

            $responseTime = (int) round(
                (microtime(true) - $start) * 1000
            );

            $body = $response->getContent();

            $responseSize = strlen($body);

            $contentType = $response->headers->get(
                'Content-Type',
                'text/html'
            );

            $httpStatus = $response->getStatusCode();

            /*
             * Store response information in the local cache.
             */
            LocalCacheEntry::updateOrCreate(
                [
                    'cache_key' => $cacheKey,
                ],
                [
                    'url' => $url,
                    'http_status' => $httpStatus,
                    'response_time' => $responseTime,
                    'response_size' => $responseSize,
                    'content_type' => $contentType,
                    'expires_at' => now()->addMinutes(30),
                ]
            );

            /*
             * Record cache MISS.
             */
            $cacheRequest = CacheRequest::create([
                'url' => $url,
                'method' => 'GET',
                'status' => 'MISS',
                'http_status' => $httpStatus,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'content_type' => $contentType,
            ]);

            return [
                'success' => true,
                'cache_status' => 'MISS',
                'http_status' => $httpStatus,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'content_type' => $contentType,
                'cache_age' => 0,
                'message' => 'Response fetched from the local Laravel application and stored in the simulated cache.',
                'request_id' => $cacheRequest->id,
            ];

        } catch (\Throwable $e) {

            $responseTime = (int) round(
                (microtime(true) - $start) * 1000
            );

            CacheRequest::create([
                'url' => $url,
                'method' => 'GET',
                'status' => 'BYPASS',
                'http_status' => null,
                'response_time' => $responseTime,
                'response_size' => 0,
                'content_type' => null,
            ]);

            throw new \RuntimeException(
                'Unable to process the local Laravel URL: '
                . $e->getMessage()
            );
        }
    }

    /**
     * Purge a specific URL.
     */
    public function purgeUrl(string $url): array
    {
        if (!$this->isAllowedUrl($url)) {
            throw new \InvalidArgumentException(
                'Only localhost URLs are allowed.'
            );
        }

        $cacheKey = hash('sha256', $url);

        $deleted = LocalCacheEntry::where(
            'cache_key',
            $cacheKey
        )->delete();

        $purge = CachePurge::create([
            'type' => 'URL',
            'target' => $url,
            'status' => 'SUCCESS',
            'message' => $deleted
                ? 'URL cache entry successfully purged.'
                : 'No cache entry existed for this URL.',
        ]);

        return [
            'success' => true,
            'purge' => $purge,
            'deleted' => $deleted,
        ];
    }

    /**
     * Purge all local cache entries.
     */
    public function purgeAll(): array
    {
        $count = LocalCacheEntry::count();

        LocalCacheEntry::query()->delete();

        $purge = CachePurge::create([
            'type' => 'ALL',
            'target' => null,
            'status' => 'SUCCESS',
            'message' => $count
                . ' cache entries were purged successfully.',
        ]);

        return [
            'success' => true,
            'purge' => $purge,
            'deleted' => $count,
        ];
    }

    /**
     * Allow localhost URLs only.
     */
    public function isAllowedUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (!$parsed || empty($parsed['host'])) {
            return false;
        }

        $host = strtolower($parsed['host']);

        return in_array($host, [
            '127.0.0.1',
            'localhost',
            '::1',
        ], true);
    }

    /**
     * Calculate cache analytics.
     */
    public function analytics(): array
    {
        $totalRequests = CacheRequest::count();

        $cacheHits = CacheRequest::where(
            'status',
            'HIT'
        )->count();

        $cacheMisses = CacheRequest::where(
            'status',
            'MISS'
        )->count();

        $bypassedRequests = CacheRequest::where(
            'status',
            'BYPASS'
        )->count();

        $cacheHitRatio = $totalRequests > 0
            ? round(($cacheHits / $totalRequests) * 100, 2)
            : 0;

        $totalBytes = (int) CacheRequest::sum(
            'response_size'
        );

        $averageResponseTime = round(
            (float) CacheRequest::whereNotNull(
                'response_time'
            )->avg('response_time'),
            2
        );

        $cachedBytes = (int) CacheRequest::where(
            'status',
            'HIT'
        )->sum('response_size');

        return [
            'total_requests' => $totalRequests,
            'cache_hits' => $cacheHits,
            'cache_misses' => $cacheMisses,
            'bypassed_requests' => $bypassedRequests,
            'cache_hit_ratio' => $cacheHitRatio,
            'total_bytes' => $totalBytes,
            'cached_bytes' => $cachedBytes,
            'average_response_time' => $averageResponseTime,
            'cached_entries' => LocalCacheEntry::count(),
            'total_purges' => CachePurge::count(),
        ];
    }
}
```

---

# 8. Dashboard Controller

## Step 1: Create Controller

```bash
php artisan make:controller CloudflareDashboardController
```

File:

```text
app/Http/Controllers/CloudflareDashboardController.php
```

## Step 2: Controller File

```php
<?php

namespace App\Http\Controllers;

use App\Models\CachePurge;
use App\Models\CacheRequest;
use App\Services\LocalCloudflareCacheService;

class CloudflareDashboardController extends Controller
{
    /**
     * Display cache analytics dashboard.
     */
    public function index(LocalCloudflareCacheService $cacheService)
    {
        $analytics = $cacheService->analytics();

        $recentRequests = CacheRequest::latest()
            ->take(10)
            ->get();

        $recentPurges = CachePurge::latest()
            ->take(5)
            ->get();

        return view(
            'cloudflare.dashboard',
            compact(
                'analytics',
                'recentRequests',
                'recentPurges'
            )
        );
    }
}
```

---

# 9. Cache Management Controller

## Step 1: Create Controller

```bash
php artisan make:controller CloudflareCacheController
```

File:

```text
app/Http/Controllers/CloudflareCacheController.php
```

## Step 2: Controller File

```php
<?php

namespace App\Http\Controllers;

use App\Models\CachePurge;
use App\Services\LocalCloudflareCacheService;
use Illuminate\Http\Request;

class CloudflareCacheController extends Controller
{
    /**
     * Display cache management page.
     */
    public function index(
        LocalCloudflareCacheService $cacheService
    ) {
        $analytics = $cacheService->analytics();

        return view(
            'cloudflare.cache',
            compact('analytics')
        );
    }

    /**
     * Purge all cache entries.
     */
    public function purgeAll(
        LocalCloudflareCacheService $cacheService
    ) {
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

    /**
     * Purge a specific URL.
     */
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
            $result = $cacheService->purgeUrl(
                $validated['url']
            );

            return back()->with(
                'success',
                $result['purge']->message
            );

        } catch (\Throwable $e) {

            return back()->with(
                'error',
                'URL cache purge failed: '
                . $e->getMessage()
            );
        }
    }

    /**
     * Display purge history.
     */
    public function history(Request $request)
    {
        $search = $request->input('search');

        $purges = CachePurge::query()
            ->when($search, function ($query) use ($search) {
                $query->where(
                    'type',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'target',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'status',
                        'like',
                        "%{$search}%"
                    );
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'cloudflare.history',
            compact(
                'purges',
                'search'
            )
        );
    }
}
```

---

# 10. URL Monitor Controller

## Step 1: Create Controller

```bash
php artisan make:controller CacheMonitorController
```

File:

```text
app/Http/Controllers/CacheMonitorController.php
```

## Step 2: Controller File

```php
<?php

namespace App\Http\Controllers;

use App\Models\CacheRequest;
use App\Services\LocalCloudflareCacheService;
use Illuminate\Http\Request;

class CacheMonitorController extends Controller
{
    /**
     * Display URL cache monitoring page.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $status = $request->input('status');

        $requests = CacheRequest::query()
            ->when($search, function ($query) use ($search) {
                $query->where(
                    'url',
                    'like',
                    "%{$search}%"
                );
            })
            ->when($status, function ($query) use ($status) {
                $query->where(
                    'status',
                    $status
                );
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'cloudflare.monitor',
            compact(
                'requests',
                'search',
                'status'
            )
        );
    }

    /**
     * Test a localhost URL.
     */
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
            $result = $cacheService->testUrl(
                $validated['url']
            );

            return back()
                ->with(
                    'test_result',
                    $result
                )
                ->with(
                    'success',
                    'URL tested successfully.'
                );

        } catch (\Throwable $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }
}
```

---

# 11. Web Routes

File:

```text
routes/web.php
```

Use:

```php
<?php

use App\Http\Controllers\CacheMonitorController;
use App\Http\Controllers\CloudflareCacheController;
use App\Http\Controllers\CloudflareDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cloudflare Cache Manager Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
|
| Redirect the application home page to the Cloudflare dashboard.
|
*/

Route::get('/', function () {
    return redirect()->route(
        'cloudflare.dashboard'
    );
});


/*
|--------------------------------------------------------------------------
| Cloudflare Cache Manager
|--------------------------------------------------------------------------
*/

Route::prefix('cloudflare')
    ->name('cloudflare.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            CloudflareDashboardController::class,
            'index'
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Cache Management
        |--------------------------------------------------------------------------
        */

        Route::get('/cache', [
            CloudflareCacheController::class,
            'index'
        ])->name('cache');


        /*
        |--------------------------------------------------------------------------
        | Purge All Cache
        |--------------------------------------------------------------------------
        */

        Route::post('/cache/purge-all', [
            CloudflareCacheController::class,
            'purgeAll'
        ])->name('cache.purge-all');


        /*
        |--------------------------------------------------------------------------
        | Purge Specific URL
        |--------------------------------------------------------------------------
        */

        Route::post('/cache/purge-url', [
            CloudflareCacheController::class,
            'purgeUrl'
        ])->name('cache.purge-url');


        /*
        |--------------------------------------------------------------------------
        | Purge History
        |--------------------------------------------------------------------------
        */

        Route::get('/cache/history', [
            CloudflareCacheController::class,
            'history'
        ])->name('cache.history');


        /*
        |--------------------------------------------------------------------------
        | URL Monitor
        |--------------------------------------------------------------------------
        */

        Route::get('/monitor', [
            CacheMonitorController::class,
            'index'
        ])->name('monitor');


        /*
        |--------------------------------------------------------------------------
        | Test URL
        |--------------------------------------------------------------------------
        */

        Route::post('/monitor/test', [
            CacheMonitorController::class,
            'test'
        ])->name('monitor.test');
    });
```

---

# 12. Blade Views

The project contains the following Blade views:

```text
resources/views/
│
├── layouts/
│   └── app.blade.php
│
└── cloudflare/
    ├── dashboard.blade.php
    ├── cache.blade.php
    ├── history.blade.php
    └── monitor.blade.php
```

---

## Step 1: Main Layout

File:

```text
resources/views/layouts/app.blade.php
```

The main layout provides:

- Bootstrap 5
- Navigation
- Dashboard navigation
- Cache Management navigation
- URL Monitor navigation
- Purge History navigation
- Success alerts
- Error alerts
- Responsive layout

Navigation URLs:

```text
Dashboard
/cloudflare/dashboard

Cache Management
/cloudflare/cache

URL Monitor
/cloudflare/monitor

Purge History
/cloudflare/cache/history
```

---

## Step 2: Dashboard View

File:

```text
resources/views/cloudflare/dashboard.blade.php
```

The dashboard displays:

- Total Requests
- Cache Hits
- Cache Misses
- Hit Ratio
- Bypassed Requests
- Average Response Time
- Cached Entries
- Total Purges
- Recent Requests
- Recent Purges

It also displays a **Local Simulation** message explaining that the application does not require an external Cloudflare account.

---

## Step 3: Cache Management View

File:

```text
resources/views/cloudflare/cache.blade.php
```

The cache management page provides:

### Purge Specific URL

Enter a localhost URL and purge its cache entry.

Example:

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

### Purge Everything

Removes all locally stored cache entries.

### Cache Statistics

Displays:

```text
Total Requests
Cache Hits
Cache Misses
Hit Ratio
Current Cached Entries
```

---

## Step 4: Purge History View

File:

```text
resources/views/cloudflare/history.blade.php
```

The page displays:

- Search box
- Purge type
- Target URL
- Status
- Message
- Date
- Pagination

---

## Step 5: URL Monitor View

File:

```text
resources/views/cloudflare/monitor.blade.php
```

The URL monitor provides a testing form and request history.

The test result displays:

```text
Cache Status
HTTP Status
Response Time
Response Size
Content Type
Cache Age
Request ID
```

The request history provides:

```text
URL
Status
HTTP Status
Response Time
Response Size
Content Type
Date
```

It also provides:

- Search
- Status filtering
- Pagination

---

# 13. Project Folder Structure

```text
PHP_Laravel12_CloudFlare_Cache/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── CacheMonitorController.php
│   │       ├── CloudflareCacheController.php
│   │       └── CloudflareDashboardController.php
│   │
│   ├── Models/
│   │   ├── CachePurge.php
│   │   ├── CacheRequest.php
│   │   └── LocalCacheEntry.php
│   │
│   └── Services/
│       └── LocalCloudflareCacheService.php
│
├── database/
│   └── migrations/
│       ├── xxxx_create_cache_requests_table.php
│       ├── xxxx_create_local_cache_entries_table.php
│       └── xxxx_create_cache_purges_table.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       │
│       └── cloudflare/
│           ├── dashboard.blade.php
│           ├── cache.blade.php
│           ├── history.blade.php
│           └── monitor.blade.php
│
├── routes/
│   └── web.php
│
├── storage/
│
├── .env
├── artisan
├── composer.json
├── package.json
└── README.md
```

---

# 14. Running the Project

Start the Laravel development server:

```bash
php artisan serve
```

The application will run at:

```text
http://127.0.0.1:8000
```

The root URL redirects automatically to:

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

---

# 15. Application URLs

## Dashboard

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

## Cache Management

```text
http://127.0.0.1:8000/cloudflare/cache
```

## URL Monitor

```text
http://127.0.0.1:8000/cloudflare/monitor
```

## Purge History

```text
http://127.0.0.1:8000/cloudflare/cache/history
```

---

# 16. Testing the Project

## Step 1: Open URL Monitor

Open:

```text
http://127.0.0.1:8000/cloudflare/monitor
```

---

## Step 2: Test Dashboard URL

Enter:

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

Click:

```text
Test URL
```

The first request should normally show:

```text
Cache Status: MISS
HTTP Status: 200
```

The response is now stored in the local cache.

---

## Step 3: Test the Same URL Again

Test:

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

The result should now be:

```text
Cache Status: HIT
HTTP Status: 200
```

---

## Step 4: Purge the URL

Open:

```text
http://127.0.0.1:8000/cloudflare/cache
```

Enter:

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

Click:

```text
Purge URL
```

You should see:

```text
URL cache entry successfully purged.
```

---

## Step 5: Test the URL Again

Return to:

```text
http://127.0.0.1:8000/cloudflare/monitor
```

Test the same URL again.

Result:

```text
MISS
```

The cache entry is recreated.

---

## Step 6: Test Again

Test the same URL once more.

Result:

```text
HIT
```

---

# 17. Expected Cache Flow

The complete test flow is:

```text
First Request
     ↓
   MISS
     ↓
Cache Entry Created
     ↓
Second Request
     ↓
    HIT
     ↓
Purge URL
     ↓
Cache Entry Removed
     ↓
Next Request
     ↓
   MISS
     ↓
Cache Entry Created
     ↓
Next Request
     ↓
    HIT
```

---

# 18. Expected Dashboard Statistics

For example, after several tests, the dashboard can display:

```text
Total Requests
4

Cache Hits
1

Cache Misses
1

Hit Ratio
25%

Cached Entries
1
```

The hit ratio is calculated using:

```text
(Cache Hits / Total Requests) × 100
```

Example:

```text
(1 / 4) × 100 = 25%
```

---

# 19. Purge Everything

To remove all currently cached entries:

Open:

```text
http://127.0.0.1:8000/cloudflare/cache
```

Click:

```text
Purge Everything
```

All entries from:

```text
local_cache_entries
```

will be deleted.

A purge record will also be created in:

```text
cache_purges
```

---

# 20. View Purge History

Open:

```text
http://127.0.0.1:8000/cloudflare/cache/history
```

You can see:

```text
Purge Type
Target
Status
Message
Date
```

You can also search the history.

---

# 21. View Request History

Open:

```text
http://127.0.0.1:8000/cloudflare/monitor
```

The request history shows all cache tests.

Example:

```text
#4  http://127.0.0.1:8000/cloudflare/dashboard
    HIT
    200
    1 ms

#3  http://127.0.0.1:8000/cloudflare/dashboard
    MISS
    200
    15 ms
```

---

# 22. Reset Project Test Data

If old testing data is affecting the statistics, open Laravel Tinker:

```bash
php artisan tinker
```

Run:

```php
\App\Models\CacheRequest::truncate();
\App\Models\LocalCacheEntry::truncate();
\App\Models\CachePurge::truncate();
```

Then exit:

```text
exit
```

Refresh:

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

The dashboard should now show:

```text
Total Requests: 0
Cache Hits: 0
Cache Misses: 0
Hit Ratio: 0%
Cached Entries: 0
Total Purges: 0
```

---

# 23. Important Local Simulation Note

This project intentionally does **not** make external HTTP requests to:

```text
http://127.0.0.1:8000
```

from inside the same Laravel server process.

Instead, the service internally dispatches the Laravel route.

This avoids a self-request timeout/deadlock when using:

```bash
php artisan serve
```

The URL monitor therefore works correctly with the local Laravel application.

---

# 24. Allowed URLs

For the local cache simulation, only localhost URLs are allowed.

Supported hosts:

```text
127.0.0.1
localhost
::1
```

Examples:

```text
http://127.0.0.1:8000/
http://127.0.0.1:8000/cloudflare/dashboard
http://localhost:8000/cloudflare/dashboard
```

External URLs are intentionally rejected.

---

# 25. Cloudflare API Requirement

A real Cloudflare API token is **not required** for this project.

You do not need to configure:

```env
CLOUDFLARE_API_TOKEN=
CLOUDFLARE_ZONE_ID=
```

You also do not need:

- Cloudflare domain
- Cloudflare Zone
- DNS configuration
- Cloudflare API permissions

The project uses a local database-backed cache simulation.

---

# 26. Useful Artisan Commands

Start the application:

```bash
php artisan serve
```

Clear application cache:

```bash
php artisan optimize:clear
```

Run migrations:

```bash
php artisan migrate
```

Reset database:

```bash
php artisan migrate:fresh
```

Check routes:

```bash
php artisan route:list
```

Open Tinker:

```bash
php artisan tinker
```

---

# 27. Troubleshooting

## Database Connection Error

Check `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloudflare_cache
DB_USERNAME=root
DB_PASSWORD=
```

Make sure MySQL is running in XAMPP.

---

## Migration Not Working

Run:

```bash
php artisan optimize:clear
php artisan migrate
```

---

## Application Key Error

Run:

```bash
php artisan key:generate
```

---

## Old Cache Data Appears

Reset the test data:

```bash
php artisan tinker
```

Then:

```php
\App\Models\CacheRequest::truncate();
\App\Models\LocalCacheEntry::truncate();
\App\Models\CachePurge::truncate();
```

---

## URL Not Allowed

Make sure the URL uses localhost:

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

or:

```text
http://localhost:8000/cloudflare/dashboard
```

Do not enter an external website URL.

---

# 28. Main Functionalities Added

## 1. 📊 Cloudflare Cache Analytics & Performance Dashboard

The dashboard provides:

- Total requests
- Cache hits
- Cache misses
- Cache hit ratio
- Bypassed requests
- Average response time
- Cached entries
- Total purges
- Recent request history
- Recent purge history

---

## 2. ⚡ Cache Purge Management & History

The cache management system provides:

- Purge specific URL
- Purge all cache
- Persistent purge records
- Purge status
- Purge messages
- Search
- Pagination
- Purge history

---

## 3. 🔎 URL Cache Testing & Status Monitoring

The URL monitoring system provides:

- Localhost URL testing
- Cache HIT detection
- Cache MISS detection
- BYPASS detection
- HTTP status monitoring
- Response-time monitoring
- Response-size monitoring
- Content-type monitoring
- Cache-age monitoring
- Request IDs
- Search
- Status filtering
- Pagination

---

# 29. Project Objective

The main objective of this project is to demonstrate a **Cloudflare-style cache management workflow inside Laravel 12** without requiring a real Cloudflare account.

The project demonstrates:

- Laravel MVC architecture
- Laravel service classes
- Eloquent models
- Database migrations
- Form validation
- Dependency injection
- Cache management
- Cache HIT/MISS logic
- Cache expiration
- Cache purge operations
- Request monitoring
- Performance analytics
- Search and filtering
- Pagination
- Bootstrap 5 dashboard development

---

# 30. Output

## Cloudflare Cache Dashboard

```text
http://127.0.0.1:8000/cloudflare/dashboard
```

The dashboard displays:

<img width="1897" height="1023" alt="Screenshot 2026-09-30 100920" src="https://github.com/user-attachments/assets/14bc81cd-9795-4ad8-a9a3-9ccaca8d823d" />

---

## Cache Management

```text
http://127.0.0.1:8000/cloudflare/cache
```

Provides:

<img width="1917" height="1026" alt="Screenshot 2026-09-30 100902" src="https://github.com/user-attachments/assets/3763305d-e13a-4357-bf79-f7dd9abda9fe" />

---

## URL Monitor

```text
http://127.0.0.1:8000/cloudflare/monitor
```

Provides:

<img width="1917" height="1025" alt="Screenshot 2026-09-30 100947" src="https://github.com/user-attachments/assets/38ebb38b-6e96-46fd-ba10-e8cf0b680f9e" />

---

## Purge History

```text
http://127.0.0.1:8000/cloudflare/cache/history
```

Provides:

<img width="1917" height="1025" alt="Screenshot 2026-09-30 100839" src="https://github.com/user-attachments/assets/4a7ce1ec-aa98-42d0-ad85-f5bc77dc856f" />

---

# 31. Project Ready

Your `PHP_Laravel12_CloudFlare_Cache` project is now set up as a **local Cloudflare cache management and monitoring application**.

The complete workflow is:

```text
📊 Analytics Dashboard
        ↓
🔎 Test Local URL
        ↓
MISS
        ↓
💾 Cache Entry Created
        ↓
🔎 Test Same URL
        ↓
HIT
        ↓
⚡ Purge URL
        ↓
🗑️ Cache Entry Removed
        ↓
🔎 Test Again
        ↓
MISS
        ↓
💾 Cache Entry Created
        ↓
HIT
```
