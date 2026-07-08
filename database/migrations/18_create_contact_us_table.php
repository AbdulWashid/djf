<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->enum('employees', ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'])->nullable();
            $table->string('title')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->enum('status', ['new', 'read', 'pending', 'responded', 'closed'])->default('new');
            $table->string('reply_subject')->nullable();
            $table->longText('reply_message')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->uuid('replied_by_user_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_us');
    }
};