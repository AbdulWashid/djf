<?php

namespace App\Models;

use App\Traits\HasUserStamp;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employer extends Model implements hasMedia
{

    use InteractsWithMedia;
//    use HasUlids;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'website',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_active',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile();
    }

    public function openings() {
        return $this->hasMany(Opening::class);
    }


    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
