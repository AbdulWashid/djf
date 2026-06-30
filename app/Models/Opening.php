<?php

namespace App\Models;

use App\Enums\EmploymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Opening extends Model
{
    protected $casts = [
            'expected_nationalities' => 'array',
            'meta_keywords' => 'array',
            'status' => 'boolean',
            'job_type' => EmploymentType::class,
        ];

    protected $fillable = [
        'employer_id',
        'job_category_id',
        'title',
        'slug',
        'description',
        'responsibilities',
        'skills',
        'benefits',
        'meta_title',
        'meta_keywords',
        'twitter_tags',
        'og_tags',
        'meta_description',
        'job_type',
        'location',
        'salary_range',
        'gender',
        'expected_nationalities',
        'required_experience',
        'featured',
        'status',
    ];
    // public function applications()
    // {
    //     return $this->hasMany(JobApplication::class);
    // }
    public function applications()
    {
        return $this->hasMany(JobApplications::class, 'opening_id');
    }
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }
    public function job_category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class);
    }

    // featured
    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }
  // featured
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('feature_jobs');
            Cache::forget('recent_jobs');
        });

        static::deleted(function () {
            Cache::forget('feature_jobs');
            Cache::forget('recent_jobs');
        });
    }
    

}
