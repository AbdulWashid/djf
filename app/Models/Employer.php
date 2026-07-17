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
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

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
        'slug',
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

    public function sendPasswordResetNotification($token): void
    {
        $url = route('employer.password.reset', [
            'token' => $token,
            'email' => $this->email,
        ]);

        $this->notify(new class($url) extends ResetPassword {
            public function __construct(public string $url) {}

            public function toMail($notifiable): MailMessage
            {
                return (new MailMessage)
                    ->subject('Reset Password')
                    ->line('Click the button below to reset your password.')
                    ->action('Reset Password', $this->url);
            }
        });
    }
}
