<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers');
            $table->foreignId('job_category_id')->constrained('job_categories');
            $table->string('title');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->longText('responsibilities')->nullable();
            $table->longText('skills')->nullable();
            $table->longText('benefits')->nullable();
            $table->string('meta_title');
            $table->text('meta_keywords')->nullable();
            $table->string('meta_description');
            $table->string('job_type');
            $table->string('location');
            $table->string('salary_range');
            $table->string('gender');
            $table->longText('expected_nationalities')->nullable();
            $table->string('required_experience');
            $table->boolean('featured')->default(false);
            $table->boolean('status')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->longText('twitter_tags')->nullable();
            $table->longText('og_tags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('openings');
    }
};