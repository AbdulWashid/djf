<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

}
