<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class JobListingContent extends Model
{
    protected $fillable = [
        'without_filter',
        'location',
        'category',
        'location_category',
    ];

    protected $casts = [
        'without_filter' => 'array',
        'location' => 'array',
        'category' => 'array',
        'location_category' => 'array',
    ];
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('job_listing_content');
        });

        static::deleted(function () {
            Cache::forget('job_listing_content');
        });
    }
}
