<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.frontend.main')] class extends Component {
    #[Locked]
    public string $token = '';

    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $type = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');

        $this->type = request()->routeIs('employer.password.reset') ? 'employer' : 'candidate';
        view()->share('pageTitle', $this->type == 'employer' ? 'Reset Employer Password | Dubai Job Finder' : 'Reset Candidate Password | Dubai Job Finder');

        view()->share('pageDescription', $this->type == 'employer' ? 'Create a new password for your employer account and securely regain access to your Dubai Job Finder hiring dashboard.' : 'Set a new password for your candidate account to securely access your Dubai Job Finder profile, resume, and job applications.');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $broker = $this->type === 'employer' ? 'employers' : 'candidates';

        $status = Password::broker($broker)->reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user) {
                $user
                    ->forceFill([
                        'password' => Hash::make($this->password),
                        'remember_token' => Str::random(60),
                    ])
                    ->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute($this->type . '.login', navigate: false);
    }
}; ?>

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

                    {{ $type === 'employer' ? 'Employer Account Recovery' : 'Candidate Account Recovery' }}
                </span>

                <h1 class="text-4xl font-bold text-primary-800 mb-4">
                    Reset Your Password
                </h1>

                <p class="text-lg text-gray-600 mb-8">
                    {{ $type === 'employer'
                        ? 'Secure your employer account and regain access to your hiring dashboard.'
                        : 'Create a new password and continue your job search journey.' }}
                </p>

                <div class="space-y-6">

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">🔒</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Secure Password
                            </h3>
                            <p class="text-gray-600">
                                Choose a strong password to keep your account protected.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">⚡</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Quick Recovery
                            </h3>
                            <p class="text-gray-600">
                                Reset your password and access your account immediately.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">🛡️</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Protected Account
                            </h3>
                            <p class="text-gray-600">
                                Your reset link is encrypted and valid only for a limited time.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Reset Password Form --}}
            <div class="col-lg-6 mt-4">

                <div class="bg-white shadow-lg border border-gray-100 rounded-xl p-8">

                    <div class="mb-6">

                        <h2 class="text-2xl font-bold text-primary-800">
                            {{ $type === 'employer' ? 'Employer Password Reset' : 'Candidate Password Reset' }}
                        </h2>

                        <p class="text-gray-500 mt-1">
                            Create a new password for your account.
                        </p>

                    </div>

                    <form wire:submit="resetPassword">

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium">
                                Email Address
                            </label>

                            <input wire:model="email" type="email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-primary-600 focus:border-primary-600"
                                readonly>

                            @error('email')
                                <span class="text-sm text-danger">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium">
                                New Password
                            </label>

                            <input wire:model.defer="password" type="password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-primary-600 focus:border-primary-600"
                                placeholder="Enter new password">

                            @error('password')
                                <span class="text-sm text-danger">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-6">
                            <label class="block mb-2 text-sm font-medium">
                                Confirm Password
                            </label>

                            <input wire:model.defer="password_confirmation" type="password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-primary-600 focus:border-primary-600"
                                placeholder="Confirm new password">

                            @error('password_confirmation')
                                <span class="text-sm text-danger">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-3 text-white font-medium bg-primary-800 hover:bg-primary-700 rounded-md transition">

                            <span wire:loading.remove>
                                Reset Password
                            </span>

                            <span wire:loading>
                                Resetting...
                            </span>
                        </button>
                        <div class="text-center mt-5">

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
