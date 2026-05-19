<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question',
        'answer',
        'section',
        'sort',
        'status',
    ];

    // get active
    public function scopeActive($query)
    {
        return $query->where('status', 1)->orderBy('sort');
    }


    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
