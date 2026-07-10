<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name'];
    public function openings(): HasMany
    {
        return $this->hasMany(Opening::class);
    }
}

