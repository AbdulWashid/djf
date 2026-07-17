<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    protected $fillable = [
        'name',
        'flag',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    // Active Nationality
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
