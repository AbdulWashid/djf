<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name');
            $table->text('description')->nullable();
            $table->string('subject_type');
            $table->uuid('subject_id')->nullable();
            $table->string('event');
            $table->string('causer_type')->nullable();
            $table->uuid('causer_id')->nullable();
            $table->longText('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('log_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};