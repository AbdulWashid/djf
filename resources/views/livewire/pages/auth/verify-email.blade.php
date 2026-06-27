<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Employer;
use App\Models\Candidate;
use Illuminate\Support\Facades\RateLimiter;

new #[Layout('components.frontend.main')] class extends Component {
    public string $type;
    public string $email = '';
    public function mount()
    {
        $this->type = request()->routeIs('employer.*') ? 'employer' : 'candidate';
    }

    public function sendVerification(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $this->type === 'employer' ? Employer::where('email', $this->email)->first() : Candidate::where('email', $this->email)->first();

        $key = 'verify-email:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many attempts. Please try again later.');
            return;
        }

        RateLimiter::hit($key, 300);

        if (!$user) {
            $this->addError('email', $this->type . ' not found.');
            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->addError('email', 'Email already verified.');
            return;
        }

        $user->sendEmailVerificationNotification();

        session()->flash('status', 'Verification email sent successfully.');
    }
};
?>

<div class="bg-gray-50 py-16 min-h-screen flex items-center">
    <div class="container mx-auto px-4">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <strong>Please correct the following errors:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row items-center">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
                    <div class="text-sm text-green-700 mt-1">
                        {{ session('status') }}
                    </div>
                </div>
            @endif
            {{-- Left Side --}}
            <div class="col-lg-6 mb-5 mb-lg-0">

                <span
                    class="inline-flex items-center px-4 py-2 mb-4 text-sm font-medium rounded-full
                    {{ $type === 'employer' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">

                    {{ $type === 'employer' ? 'Employer Verification' : 'Candidate Verification' }}

                </span>

                <h1 class="text-4xl font-bold text-primary-800 mb-4">

                    {{ $type === 'employer' ? 'Verify Your Company Account' : 'Verify Your Email Address' }}

                </h1>

                <p class="text-lg text-gray-600 mb-8">

                    {{ $type === 'employer'
                        ? 'Verify your email address to start posting jobs and managing applicants.'
                        : 'Verify your email address to begin applying for jobs and accessing your dashboard.' }}

                </p>

                <div class="space-y-6">

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">📧</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Email Verification
                            </h3>
                            <p class="text-gray-600">
                                Confirm ownership of your email address.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">🔒</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Secure Access
                            </h3>
                            <p class="text-gray-600">
                                Protect your account against unauthorized access.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">⚡</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Quick Activation
                            </h3>
                            <p class="text-gray-600">
                                One click is all it takes to activate your account.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Right Side --}}
            <div class="col-lg-6 mt-4">

                <div class="bg-white border border-gray-100 rounded-xl shadow-lg p-8">

                    <div class="text-center mb-6">

                        <div
                            class="w-20 h-20 mx-auto mb-4 rounded-full bg-primary-100 flex items-center justify-center">

                            <svg class="w-10 h-10 text-primary-800" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" />

                            </svg>

                        </div>

                        <h2 class="text-2xl font-bold text-primary-800">
                            Check Your Inbox
                        </h2>

                    </div>

                    <p class="text-gray-600 text-center mb-6">

                        We've sent a verification link to your email address.
                        Click the link to verify your account and continue.

                    </p>

                    @if (session('status'))
                        <div class="mb-6 p-4 rounded-lg border border-green-200 bg-green-50 text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="space-y-3">
                        <form wire:submit="sendVerification">
                            <div class="mb-4">
                                <input type="email" wire:model.defer="email" autocomplete="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                    placeholder="Enter your email">

                                @error('email')
                                    <span class="text-sm text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full py-3 bg-primary-800 hover:bg-primary-700 text-white rounded-md font-medium transition">

                                <span wire:loading.remove>
                                    Resend Verification Email
                                </span>

                                <span wire:loading>
                                    Sending...
                                </span>

                            </button>
                        </form>
                    </div>

                    <div class="mt-6 text-center text-sm text-gray-500">

                        Didn't receive the email?

                        <br>

                        Check your spam folder or click
                        <strong>Resend Verification Email</strong>.

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
