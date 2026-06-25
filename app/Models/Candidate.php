<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use App\Notifications\CandidateVerifyEmail;
use Filament\Models\Contracts\HasName;

class Candidate extends Authenticatable implements MustVerifyEmail, HasName
{
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'cover_letter',
        'resume_path',
        'nationality',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CandidateVerifyEmail());
    }
    public function sendPasswordResetNotification($token): void
    {
        $url = route('candidate.password.reset', [
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
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function getResumeUrlAttribute(): ?string
    {
        return $this->resume_path
            ? asset('storage/' . $this->resume_path)
            : null;
    }
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function getFilamentName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
