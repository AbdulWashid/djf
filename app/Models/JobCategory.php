<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];

    public function openings() {
        return $this->hasMany(Opening::class);
    }

    // acgive
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
