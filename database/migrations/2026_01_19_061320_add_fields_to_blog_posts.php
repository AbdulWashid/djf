<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
//            $table->json('meta_keywords')->nullable();
//            $table->text('twitter_tags')->nullable();

//            $table->text('og_tags')->nullable();


        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('twitter_tags');
            $table->dropColumn('og_tags');

            $table->dropColumn('meta_keywords');


        });
    }
};
