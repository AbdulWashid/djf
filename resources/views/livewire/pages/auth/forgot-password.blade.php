<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\RateLimiter;

new #[Layout('components.frontend.main')] class extends Component {
    public string $email = '';
    public string $type = '';

    public function mount(): void
    {
        $this->type = request()->routeIs('employer.*') ? 'employer' : 'candidate';
    }

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $broker = $this->type === 'employer' ? 'employers' : 'candidates';

        $key = 'password-reset:' . $this->email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many attempts. Try again later.');
            return;
        }

        RateLimiter::hit($key, 300);

        $status = Password::broker($broker)->sendResetLink([
            'email' => $this->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
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

            {{-- Left Recovery Content --}}
            <div class="col-lg-6 mb-5 mb-lg-0">

                <span
                    class="inline-flex items-center px-4 py-2 mb-4 text-sm font-medium rounded-full
                        {{ $type === 'employer' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">

                    {{ $type === 'employer' ? 'Employer Recovery' : 'Candidate Recovery' }}

                </span>

                <h1 class="text-4xl font-bold text-primary-800 mb-4">

                    Forgot Your Password?

                </h1>

                <p class="text-lg text-gray-600 mb-8">

                    {{ $type === 'employer'
                        ? 'Recover access to your employer dashboard and continue hiring top talent.'
                        : 'Recover your account and continue your job search journey.' }}

                </p>

                <div class="space-y-6">

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">📧</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Password Reset Link
                            </h3>
                            <p class="text-gray-600">
                                We'll send a secure password reset link to your email.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">🔒</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Secure Recovery
                            </h3>
                            <p class="text-gray-600">
                                Your reset link is encrypted and expires automatically.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">⚡</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Fast Access
                            </h3>
                            <p class="text-gray-600">
                                Regain access to your account within minutes.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Recovery Form Card --}}
            <div class="col-lg-6 mt-4">
                <div class="bg-white border border-gray-100 rounded-xl shadow-lg p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-primary-800">
                            {{ $type === 'employer' ? 'Employer Password Recovery' : 'Candidate Password Recovery' }}
                        </h2>
                        <p class="text-gray-500 mt-1">
                            Enter your email address and we'll send you a reset link.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="mb-6 p-4 rounded-lg border border-green-200 bg-green-50">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-green-700 text-sm">
                                    {{ session('status') }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="sendPasswordResetLink">
                        <div class="mb-6">
                            <label class="block mb-2 text-sm font-medium">
                                Email Address
                            </label>
                            <input type="email" wire:model.defer="email" autocomplete="email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Enter your email address">
                            @error('email')
                                <span class="text-sm text-danger">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-3 bg-primary-800 hover:bg-primary-700 text-white rounded-md font-medium transition">
                            <span wire:loading.remove>
                                Send Reset Link
                            </span>

                            <span wire:loading>
                                Sending...
                            </span>
                        </button>
                        <div class="mt-5 text-center">
                            <span class="text-gray-500">
                                Remember your password?
                            </span>
                            <a href="{{ route($type . '.login') }}"
                                class="font-medium text-primary-600 hover:text-primary-800">
                                Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
