<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use App\Notifications\EmployerVerifyEmail;

class Employer extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use InteractsWithMedia;
    use Notifiable;

    protected $guard = 'employer';

    protected $fillable = [
        'name',
        'logo',
        'description',
        'website',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile();
    }

    public function openings()
    {
        return $this->hasMany(Opening::class);
    }
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new EmployerVerifyEmail());
    }
}
