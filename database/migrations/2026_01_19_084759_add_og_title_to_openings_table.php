<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('openings', function (Blueprint $table) {
            $table->text('twitter_tags')->nullable();

            $table->text('og_tags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('openings', function (Blueprint $table) {
            $table->dropColumn('twitter_tags');
            $table->dropColumn('og_tags');

        });
    }
};
