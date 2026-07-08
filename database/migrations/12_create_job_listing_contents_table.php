<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listing_contents', function (Blueprint $table) {
            $table->id();
            $table->longText('without_filter')->nullable();
            $table->longText('location')->nullable();
            $table->longText('category')->nullable();
            $table->longText('location_category')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listing_contents');
    }
};