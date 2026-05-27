<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->string('model_id', 36)->nullable()->change();
            $table->string('user_id', 36)->nullable()->change();
        });

        Schema::table('folder_has_models', function (Blueprint $table) {
            $table->string('model_id', 36)->change();
        });

        Schema::table('media_has_models', function (Blueprint $table) {
            $table->string('model_id', 36)->change();
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('folder_has_models', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change();
        });

        Schema::table('media_has_models', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change();
        });
    }
};
