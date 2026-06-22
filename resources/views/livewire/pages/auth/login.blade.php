<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.frontend.main')] class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

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

        $redirect = $this->type === 'employer' ? route('employer.dashboard') : route('candidate.dashboard');

        $this->redirectIntended(default: $redirect, navigate: true);
    }
};
?>

<div class="bg-gray-50 py-16 min-h-screen flex items-center">
    <div class="container mx-auto px-4">
        <div class="row items-center">

            {{-- Left Content --}}
            <div class="col-lg-6 mb-5 mb-lg-0">

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
            <div class="col-lg-6">

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

                    <form wire:submit="login">

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">
                                Email Address
                            </label>

                            <input type="email" wire:model="email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Enter your email">

                            @error('email')
                                <span class="text-sm text-red-500">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">
                                Password
                            </label>

                            <input type="password" wire:model="password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Enter password">

                            @error('password')
                                <span class="text-sm text-red-500">
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
                                <a href="{{ route($type . '.password.request') }}" wire:navigate
                                    class="text-sm text-primary-600 hover:text-primary-800">

                                    Forgot Password?
                                </a>
                            @endif

                        </div>

                        <button type="submit"
                            class="w-full py-3 bg-primary-800 hover:bg-primary-700 text-white rounded-md font-medium transition">

                            {{ $type === 'employer' ? 'Login as Employer' : 'Login as Candidate' }}
                        </button>

                        <div class="text-center mt-5">

                            <span class="text-gray-500">
                                Don't have an account?
                            </span>

                            <a href="{{ route($type . '.register') }}" wire:navigate
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
