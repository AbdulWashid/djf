<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Employer;
use App\Models\Candidate;

new #[Layout('components.frontend.main')] class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showVerifyButton = false;
    public string $type = '';

    public function mount(): void
    {
        $this->type = request()->routeIs('employer.login') ? 'employer' : 'candidate';
    }

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($this->type === 'employer') {
            $employer = Employer::where('email', $this->email)->first();

            if (!$employer) {
                throw ValidationException::withMessages([
                    'email' => 'Invalid credentials.',
                ]);
            }

            // Email verification check
            if (!$employer->hasVerifiedEmail()) {
                $this->showVerifyButton = true;
                throw ValidationException::withMessages([
                    'email' => 'Please verify your email address first.',
                ]);
            }

            // Account status check
            if (!$employer->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'Your account is pending approval by the administrator.',
                ]);
            }
        } else {
            $candidate = Candidate::where('email', $this->email)->first();
            if (!$candidate) {
                throw ValidationException::withMessages([
                    'email' => 'Invalid credentials.',
                ]);
            }
            // Email verification check
            if (!$candidate->hasVerifiedEmail()) {
                $this->showVerifyButton = true;
                throw ValidationException::withMessages([
                    'email' => 'Please verify your email address first.',
                ]);
            }
            // Account status check
            if (!$candidate->status) {
                throw ValidationException::withMessages([
                    'email' => 'Your account is pending approval by the administrator.',
                ]);
            }
        }
        if (
            !Auth::guard($this->type)->attempt(
                [
                    'email' => $this->email,
                    'password' => $this->password,
                ],
                $this->remember,
            )
        ) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Session::regenerate();

        $redirect = $this->type === 'employer' ? route('employer.profile') : route('candidate.profile');

        $this->redirectIntended(default: $redirect, navigate: false);
    }
};
?>

<div class="bg-gray-50 py-16 min-h-screen flex items-center">
    <div class="container mx-auto px-4">
        <div class="row items-center">
            @if (session('registration-success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
                    <div class="font-medium text-green-800">
                        Registration Successful
                    </div>

                    <div class="text-sm text-green-700 mt-1">
                        {{ session('registration-success') }}
                    </div>
                </div>
            @endif
            {{-- Left Content --}}
            <div class="col-lg-6 mb-5 mb-lg-0 mt-4">

                <span
                    class="inline-flex items-center px-4 py-2 mb-4 text-sm font-medium rounded-full
                        {{ $type === 'employer' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">

                    {{ $type === 'employer' ? 'Employer Portal' : 'Candidate Portal' }}
                </span>

                <h1 class="text-4xl font-bold text-primary-800 mb-4">
                    {{ $type === 'employer' ? 'Welcome Back Employer' : 'Welcome Back Candidate' }}
                </h1>

                <p class="text-lg text-gray-600 mb-8">
                    {{ $type === 'employer'
                        ? 'Access your employer dashboard, manage jobs and review applications.'
                        : 'Access your account, track applications and discover new opportunities.' }}
                </p>

                <div class="space-y-6">

                    @if ($type === 'employer')
                        <div class="flex items-start">
                            <div class="mr-4 text-2xl">📋</div>
                            <div>
                                <h3 class="font-semibold text-lg">Manage Job Posts</h3>
                                <p class="text-gray-600">
                                    Create, edit and manage all job listings.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-2xl">👥</div>
                            <div>
                                <h3 class="font-semibold text-lg">Review Applicants</h3>
                                <p class="text-gray-600">
                                    View candidate profiles and resumes.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-2xl">📊</div>
                            <div>
                                <h3 class="font-semibold text-lg">Track Performance</h3>
                                <p class="text-gray-600">
                                    Monitor views and applications for your jobs.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start">
                            <div class="mr-4 text-2xl">💼</div>
                            <div>
                                <h3 class="font-semibold text-lg">Browse Jobs</h3>
                                <p class="text-gray-600">
                                    Discover jobs matching your skills.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-2xl">📄</div>
                            <div>
                                <h3 class="font-semibold text-lg">Track Applications</h3>
                                <p class="text-gray-600">
                                    Keep track of all submitted applications.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-2xl">🎯</div>
                            <div>
                                <h3 class="font-semibold text-lg">Get Hired Faster</h3>
                                <p class="text-gray-600">
                                    Complete your profile and attract recruiters.
                                </p>
                            </div>
                        </div>
                    @endif

                </div>

            </div>

            {{-- Login Card --}}
            <div class="col-lg-6 mt-4">

                <div class="bg-white shadow-lg border border-gray-100 rounded-xl p-8">

                    <div class="mb-6">

                        <h2 class="text-2xl font-bold text-primary-800">
                            {{ $type === 'employer' ? 'Employer Login' : 'Candidate Login' }}
                        </h2>

                        <p class="text-gray-500 mt-1">
                            Sign in to continue.
                        </p>

                    </div>

                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="login">

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">
                                Email Address
                            </label>

                            <input type="email" wire:model.defer="email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Enter your email">

                            @error('email')
                                <span class="text-sm text-danger">
                                    {{ $message }}
                                </span>
                            @enderror
                            @if ($showVerifyButton)
                                <div class="mt-2">
                                    <button type="button" wire:click="resendVerification"
                                        class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                                        Resend Verification Email
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Password -->
                        <div x-data="{ show: false }" class="mb-4">
                            <label class="block text-sm font-medium mb-2">
                                Password
                            </label>

                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" wire:model.defer="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                    placeholder="Enter password">

                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center px-3">

                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                            c4.477 0 8.268 2.943 9.542 7
                                            -1.274 4.057-5.065 7-9.542 7
                                            -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19
                                            c-4.478 0-8.268-2.943-9.543-7
                                            a9.97 9.97 0 012.223-3.592M6.228 6.228
                                            A9.956 9.956 0 0112 5c4.478 0 8.268
                                            2.943 9.543 7a9.97 9.97 0 01-4.132
                                            5.411M15 12a3 3 0 00-3-3m0 0
                                            a3 3 0 00-2.121.879M3 3l18 18" />
                                    </svg>

                                </button>
                            </div>
                            @error('password')
                                <span class="text-red-500 text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Remember -->
                        <div class="flex items-center justify-between mb-6">

                            <label class="flex items-center">
                                <input wire:model="remember" type="checkbox"
                                    class="rounded border-gray-300 text-primary-600">

                                <span class="ml-2 text-sm text-gray-600">
                                    Remember me
                                </span>
                            </label>

                            @if (Route::has($type . '.password.request'))
                                <a href="{{ route($type . '.password.request') }}"
                                    class="text-sm text-primary-600 hover:text-primary-800">

                                    Forgot Password?
                                </a>
                            @endif

                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-3 bg-primary-800 hover:bg-primary-700 text-white rounded-md font-medium transition">
                            <span wire:loading.remove>

                                {{ $type === 'employer' ? 'Login as Employer' : 'Login as Candidate' }}
                            </span>
                            <span wire:loading>
                                Logging in...
                            </span>
                        </button>

                        <div class="text-center mt-5">
                            <span class="text-gray-500">
                                Don't have an account?
                            </span>
                            <a href="{{ route($type . '.register') }}"
                                class="font-medium text-primary-600 hover:text-primary-800">

                                Register Here
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
