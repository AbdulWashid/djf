<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // faq titles
            $table->string('faq_title')->nullable();
        });
        Schema::table('static_pages', function (Blueprint $table) {
            $table->string('faq_title')->nullable();
        });

    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('faq_title');
        });
        Schema::table('static_pages', function (Blueprint $table) {
            $table->dropColumn('faq_title');
        });
    }
};
