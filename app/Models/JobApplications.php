<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplications extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'opening_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'cover_letter',
        'resume_path',
        'nationality',
        'status',
    ];

    public function opening(): BelongsTo
    {
        return $this->belongsTo(Opening::class);
    }
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function job()
    {
        return $this->belongsTo(Opening::class, 'opening_id');
    }
}
