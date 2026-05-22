<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageMeta extends Model
{
    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'twitter_tags',
        'og_tags',
    ];

    protected $casts = [
        'meta_keywords' => 'array',
    ];
}
