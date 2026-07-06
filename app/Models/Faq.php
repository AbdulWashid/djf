<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Faq extends Model
{
    use SoftDeletes;

    public const TYPE_DEFAULT = 'default';
    public const TYPE_LOCATION = 'location';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_BOTH = 'both';

    protected $fillable = [
        'question',
        'answer',
        'section',
        'type',
        'sort',
        'status',
    ];

    // get active
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1)->orderBy('sort');
    }

    public function scopeForJobsContext(Builder $query, ?string $location = null, ?int $categoryId = null): Builder
    {
        $types = [self::TYPE_DEFAULT];

        if ($location && $categoryId) {
            $types[] = self::TYPE_BOTH;
        } elseif ($location) {
            $types[] = self::TYPE_LOCATION;
        } elseif ($categoryId) {
            $types[] = self::TYPE_CATEGORY;
        }

        return $query
            ->where('section', 'jobs')
            ->where('status', true)
            ->whereIn('type', $types)
            ->orderByRaw("CASE type WHEN 'both' THEN 1 WHEN 'location' THEN 2 WHEN 'category' THEN 3 ELSE 4 END")
            ->orderBy('sort');
    }

    public function renderForJobContext(?string $categoryName = null, ?string $placeName = null): array
    {
        $replacements = [
            '{category-name}' => $categoryName ?? '',
            '{place-name}' => $placeName ?? '',
        ];

        return [
            'question' => Str::of($this->question)->replace($replacements)->toString(),
            'answer' => Str::of($this->answer)->replace($replacements)->toString(),
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DEFAULT => 'Default',
            self::TYPE_LOCATION => 'Location',
            self::TYPE_CATEGORY => 'Category',
            self::TYPE_BOTH => 'Both (Location + Category)',
        ];
    }


    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('job_faqs');
        });

        static::deleted(function () {
            Cache::forget('job_faqs');
        });
    }
}
