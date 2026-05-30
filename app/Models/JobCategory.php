<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class JobCategory extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];

    public function openings() {
        return $this->hasMany(Opening::class);
    }

    public function getLogoAttribute($value): ?string
    {
        $media = $this->getFirstMediaUrl('job-categories');

        if (! blank($media)) {
            return $media;
        }

        return $value;
    }

    // acgive
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
