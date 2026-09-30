<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_cache_entries', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->unique();
            $table->text('url');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('response_time')->nullable();
            $table->unsignedBigInteger('response_size')->nullable();
            $table->string('content_type')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_cache_entries');
    }
};