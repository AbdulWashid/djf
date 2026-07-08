<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filament_exceptions_table', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('code');
            $table->longText('message')->nullable();
            $table->string('file');
            $table->integer('line');
            $table->longText('trace')->nullable();
            $table->string('method');
            $table->string('path');
            $table->text('query')->nullable();
            $table->text('body')->nullable();
            $table->text('cookies')->nullable();
            $table->text('headers')->nullable();
            $table->string('ip');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filament_exceptions_table');
    }
};