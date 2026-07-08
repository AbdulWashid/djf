<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->uuid('model_id');
            $table->uuid('uuid')->nullable();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type');
            $table->string('disk');
            $table->string('conversions_disk');
            $table->unsignedBigInteger('size');
            $table->longText('manipulations')->nullable();
            $table->longText('custom_properties')->nullable();
            $table->longText('generated_conversions')->nullable();
            $table->longText('responsive_images')->nullable();
            $table->integer('order_column');
            $table->index('order_column');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};