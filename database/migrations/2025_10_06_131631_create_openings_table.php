<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->foreignId('job_category_id')->constrained('job_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('responsibilities')->nullable();
            $table->text('skills')->nullable();
            $table->text('benefits')->nullable();
            $table->string('meta_title', 60)->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('job_type');
            $table->string('location');
            $table->string('salary_range');
            $table->string('gender');
            $table->json('expected_nationalities');
            $table->string('required_experience');

            $table->boolean('featured')->default(false);
            $table->boolean('status');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('openings');
    }
};
