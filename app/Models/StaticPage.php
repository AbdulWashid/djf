<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaticPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'faqs',
        'faq_title',
        'meta_keywords',
        'meta_description',
        'status',
        'twitter_tags',
        'og_tags'
    ];

    protected function casts(): array
    {
        return [
            'meta_keywords' => 'array',
            'status' => 'boolean',
            'faqs' => 'array',
        ];
    }
}
