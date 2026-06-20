<?php

use App\Models\Employer;
use App\Models\Candidate;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.frontend.main')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $type = '';

    public function mount(): void
    {
        $this->type = request()->routeIs('employer.register') ? 'employer' : 'candidate';
    }

    public function register(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if ($this->type === 'employer') {
            $rules['email'] = ['required', 'email', 'unique:employers,email'];

            $user = Employer::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'is_active' => false,
            ]);

            event(new Registered($user));

            Auth::guard('employer')->login($user);

            $this->redirect(route('employer.dashboard'), navigate: true);

            return;
        }

        $rules['email'] = ['required', 'email', 'unique:candidates,email'];

        $user = Candidate::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::guard('candidate')->login($user);

        $this->redirect(route('candidate.dashboard'), navigate: true);
    }
}; ?>
<div class="bg-gray-50 py-16 min-h-screen">
    <div class="container mx-auto px-4">
        <div class="row items-center">

            <!-- Left Content -->
            <div class="col-lg-6 mb-5 mb-lg-0">

                <span
                    class="inline-flex items-center px-4 py-2 mb-4 text-sm font-medium rounded-full {{ $type === 'employer' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                    {{ $type === 'employer' ? 'Employer Account' : 'Candidate Account' }}
                </span>

                <h1 class="text-4xl font-bold text-primary-800 mb-4">
                    {{ $type === 'employer' ? 'Hire the Best Talent' : 'Find Your Dream Job' }}
                </h1>

                <p class="text-gray-600 mb-8 text-lg">
                    {{ $type === 'employer'
                        ? 'Create your employer account and start posting jobs, managing applicants, and finding qualified candidates.'
                        : 'Create your candidate account and apply to thousands of jobs from top companies.' }}
                </p>

                <div class="space-y-6">

                    @if ($type === 'employer')
                        <div class="flex items-start">
                            <div class="mr-4 text-primary-800 text-2xl">🏢</div>
                            <div>
                                <h3 class="font-semibold text-lg">Post Unlimited Jobs</h3>
                                <p class="text-gray-600">
                                    Reach thousands of active job seekers.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-primary-800 text-2xl">👥</div>
                            <div>
                                <h3 class="font-semibold text-lg">Manage Applicants</h3>
                                <p class="text-gray-600">
                                    Review applications from a single dashboard.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-primary-800 text-2xl">🚀</div>
                            <div>
                                <h3 class="font-semibold text-lg">Build Your Brand</h3>
                                <p class="text-gray-600">
                                    Showcase your company and attract top talent.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start">
                            <div class="mr-4 text-primary-800 text-2xl">💼</div>
                            <div>
                                <h3 class="font-semibold text-lg">Apply to Jobs</h3>
                                <p class="text-gray-600">
                                    Explore opportunities from leading employers.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-primary-800 text-2xl">📄</div>
                            <div>
                                <h3 class="font-semibold text-lg">Create Your Profile</h3>
                                <p class="text-gray-600">
                                    Let recruiters discover your skills.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="mr-4 text-primary-800 text-2xl">🎯</div>
                            <div>
                                <h3 class="font-semibold text-lg">Track Applications</h3>
                                <p class="text-gray-600">
                                    Monitor all your job applications in one place.
                                </p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <!-- Registration Form -->
            <div class="col-lg-6">
                <div class="bg-white shadow-lg rounded-xl border border-gray-100 p-8">

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-primary-800">
                            {{ $type === 'employer' ? 'Create Employer Account' : 'Create Candidate Account' }}
                        </h2>

                        <p class="text-gray-500 mt-1">
                            {{ $type === 'employer' ? 'Start hiring talent today.' : 'Start your job search journey today.' }}
                        </p>

                        <p class="text-gray-500 mt-1">
                            Fill in your details below.
                        </p>
                    </div>

                    <form wire:submit="register">

                        <!-- Name -->
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium">
                                Full Name
                            </label>

                            <input type="text" wire:model="name"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Enter your full name">

                            @error('name')
                                <span class="text-red-500 text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium">
                                Email Address
                            </label>

                            <input type="email" wire:model="email"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Enter your email">

                            @error('email')
                                <span class="text-red-500 text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium">
                                Password
                            </label>

                            <input type="password" wire:model="password"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Enter password">

                            @error('password')
                                <span class="text-red-500 text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-6">
                            <label class="block mb-2 text-sm font-medium">
                                Confirm Password
                            </label>

                            <input type="password" wire:model="password_confirmation"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                placeholder="Confirm password">

                            @error('password_confirmation')
                                <span class="text-red-500 text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Button -->
                        <button type="submit"
                            class="w-full bg-primary-800 hover:bg-primary-700 text-white py-3 rounded-md font-medium transition">

                            {{ $type === 'employer' ? 'Register as Employer' : 'Register as Candidate' }}
                        </button>

                        <div class="text-center mt-5">

                            <span class="text-gray-500">
                                Already have an account?
                            </span>

                            <a href="{{ route($type . '.login') }}" wire:navigate
                                class="font-medium text-primary-600 hover:text-primary-800">

                                Login Here
                            </a>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
