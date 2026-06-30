<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Facades\Cache;

class JobCategory extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'logo',
        'status',
    ];

    protected $casts = [
        'position' => 'integer',
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

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('job_categories_active_count');
            Cache::forget('job_categories_home');
            for ($i = 1; $i <= 20; $i++) {
                Cache::forget("job_categories_all_page_{$i}");
            }
        });

        static::deleted(function () {
            Cache::forget('job_categories_active_count');
            Cache::forget('job_categories_home');
            for ($i = 1; $i <= 20; $i++) {
                Cache::forget("job_categories_all_page_{$i}");
            }
        });
    }
}
