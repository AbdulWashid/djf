<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->uuid('blog_author_id')->nullable();
            $table->ulid('blog_category_id')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('title');
            $table->string('slug');
            $table->longText('content_raw')->nullable();
            $table->longText('content_html')->nullable();
            $table->text('content_overview')->nullable();
            $table->enum('status', ['draft', 'pending', 'published', 'archived'])->default('draft');
            $table->date('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('last_published_at')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale');
            $table->longText('options')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('reading_time')->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->longText('faqs')->nullable();
            $table->string('faq_title')->nullable();
            $table->longText('twitter_tags')->nullable();
            $table->longText('og_tags')->nullable();
            $table->longText('meta_keywords')->nullable();
            $table->index('locale');
            $table->foreign('blog_author_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('blog_category_id')->references('id')->on('blog_categories')->nullOnDelete();
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->fullText(['title', 'content_overview'], 'blog_posts_title_content_overview_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};