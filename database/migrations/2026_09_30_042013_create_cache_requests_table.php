<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache_requests', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('method')->default('GET');
            $table->string('status')->default('MISS');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('response_time')->nullable();
            $table->unsignedBigInteger('response_size')->nullable();
            $table->string('content_type')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_requests');
    }
};