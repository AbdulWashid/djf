<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            // add twitter card field
            $table->text('twitter_tags')->nullable();
            // og image field
            $table->text('og_tags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            //
            $table->dropColumn('twitter_tags');
            $table->dropColumn('og_tags');
        });
    }
};
