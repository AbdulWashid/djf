<?php

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Employer;
use App\Models\Candidate;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    public string $name = ''; // employer
    public string $first_name = ''; // candidate
    public string $last_name = ''; // candidate
    public string $email = ''; // both
    public string $password = ''; // both
    public string $password_confirmation = ''; // both
    public string $description = ''; // employer
    public string $website = ''; // employer
    public string $phone = ''; // both
    public string $address = ''; // employer
    public string $city = ''; // employer
    public string $state = ''; // employer
    public string $country = ''; // employer
    public string $postal_code = ''; // employer
    public $logo; // employer
    public string $nationality = ''; // candidate
    public $resume; // candidate
    public string $cover_letter = ''; // candidate
    public string $type = '';

    public function mount(): void
    {
        $this->type = request()->routeIs('employer.register') ? 'employer' : 'candidate';
        view()->share('pageTitle', $this->type == 'empployer' ? 'Employer Registration | Create a Hiring Account - Dubai Job Finder' : 'Candidate Registration | Sign Up & Find Jobs - Dubai Job Finder');
        view()->share('pageDescription', $this->type == 'empployer' ? 'Register as an employer on Dubai Job Finder. Create your company profile, post jobs, and start connecting with qualified professionals in Dubai today.' : 'Create your free candidate account on Dubai Job Finder. Upload your CV, apply to top vacancies, and get noticed by leading employers in Dubai.');
    }

    public function register(): void
    {
        try {
            DB::beginTransaction();

            if ($this->type === 'employer') {
                $rules = [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'unique:employers,email'],
                    'description' => ['nullable', 'string'],
                    'website' => ['nullable', 'url'],
                    'phone' => ['nullable', 'regex:/^[0-9+\-\s()]+$/', 'min:10', 'max:15'],
                    'address' => ['nullable', 'string', 'max:255'],
                    'city' => ['nullable', 'string', 'max:100'],
                    'state' => ['nullable', 'string', 'max:100'],
                    'country' => ['nullable', 'string', 'max:100'],
                    'postal_code' => ['nullable', 'string', 'max:20'],
                    'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ];

                $this->validate($rules);

                $logoPath = null;
                if ($this->logo) {
                    $logoPath = $this->logo->store('employers', 'public');
                }

                $user = Employer::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'description' => $this->description,
                    'website' => $this->website,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'city' => $this->city,
                    'state' => $this->state,
                    'country' => $this->country,
                    'postal_code' => $this->postal_code,
                    'password' => Hash::make($this->password),
                    'logo' => $logoPath,
                    'is_active' => false,
                ]);

                event(new Registered($user));
                DB::commit();

                session()->flash('registration-success', 'Your employer account has been created successfully. A verification email has been sent to your email address. Please verify your email before logging in. Your account will be reviewed and activated by our team.');
                $this->redirect(route('employer.login'), navigate: false);
                return;
            }

            // Candidate Logic
            $rules = [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:candidates,email'],
                'phone' => ['nullable', 'regex:/^[0-9+\-\s()]+$/', 'min:10', 'max:15'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'resume' => ['required', 'mimes:pdf,doc,docx', 'max:5120'],
                'cover_letter' => ['nullable', 'string', 'max:250'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ];

            $this->validate($rules);

            $resumePath = null;
            if ($this->resume) {
                $resumePath = $this->resume->store('resumes', 'public');
            }

            $user = Candidate::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'nationality' => $this->nationality,
                'cover_letter' => $this->cover_letter,
                'resume_path' => $resumePath,
                'status' => false,
                'password' => Hash::make($this->password),
            ]);

            event(new Registered($user));
            DB::commit();

            session()->flash('registration-success', 'Your account has been created successfully. A verification email has been sent to your email address. Please verify your email before logging in. Your account will be reviewed and activated by our team.');
            $this->redirect(route('candidate.login'), navigate: false);
            return;
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage());
            session()->flash('registration-error', 'An unexpected error occurred during registration. Please try again later.');
        }
    }
}; ?>

<div class="bg-gray-50 py-16 min-h-screen">
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
        <div class="row ">
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

            @if (session('registration-error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="font-medium text-red-800">
                        Registration Failed
                    </div>
                    <div class="text-sm text-red-700 mt-1">
                        {{ session('registration-error') }}
                    </div>
                </div>
            @endif

            <!-- Left Content -->
            <div class="col-lg-6 mb-5 mb-lg-0 mt-4">
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
            <div class="col-lg-6 mt-4">
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
                        @if ($type === 'employer')
                            <!-- Name -->
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium">
                                    Company Name
                                </label>

                                <input type="text" wire:model.live="name"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror "
                                    placeholder="Enter your name">

                                @error('name')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        @elseif ($type === 'candidate')
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium">
                                    First Name
                                </label>

                                <input type="text" wire:model.live="first_name"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror "
                                    placeholder="Enter your full name">

                                @error('first_name')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium">
                                    Last Name
                                </label>

                                <input type="text" wire:model.live="last_name"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror "
                                    placeholder="Enter your last name">

                                @error('last_name')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        @endif
                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium">
                                Email Address
                            </label>

                            <input type="email" wire:model.live="email"
                                class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror"
                                placeholder="Enter your email">

                            @error('email')
                                <span class="text-danger text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        @if ($type === 'employer')
                            <div class="mb-4">
                                <label>Company Description</label>
                                <textarea wire:model.live="description" rows="1"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror"></textarea>
                                @error('description')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">

                                <div>
                                    <label>Website</label>
                                    <input type="url" wire:model.live="website"
                                        class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                    @error('website')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label>Phone</label>
                                    <input type="text" wire:model.live="phone"
                                        class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                    @error('phone')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label>Address</label>
                                    <input type="text" wire:model.live="address"
                                        class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                    @error('address')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label>City</label>
                                    <input type="text" wire:model.live="city"
                                        class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                    @error('city')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label>State</label>
                                    <input type="text" wire:model.live="state"
                                        class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                    @error('state')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label>Country</label>
                                    <input type="text" wire:model.live="country"
                                        class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                    @error('country')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label>Postal Code</label>
                                    <input type="text" wire:model.live="postal_code"
                                        class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                    @error('postal_code')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div>
                                    <label>Company Logo</label>
                                    <label
                                        class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">

                                        <svg class="w-10 h-10 mb-3 text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.9A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>

                                        <p class="text-sm text-gray-500">
                                            <span class="font-semibold">Click to upload Company Logo</span>
                                            or drag and drop
                                        </p>

                                        <p class="text-xs text-gray-400 mt-1">
                                            JPG, JPEG, PNG, WEBP (Max 2MB)
                                        </p>

                                        <input type="file" wire:model="logo" accept="image/*" class="hidden">
                                    </label>
                                    @error('logo')
                                        <span class="text-danger text-sm">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                    <div wire:loading wire:target="logo" class="mt-2 text-blue-600">
                                        Uploading...
                                    </div>

                                    @if ($logo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                        <img loading="lazy" src="{{ $logo->temporaryUrl() }}"
                                            class="h-20 w-20 rounded object-cover mt-2">
                                    @endif
                                </div>
                            </div>
                        @elseif ($type === 'candidate')
                            <div class="mb-4">
                                <label>Phone</label>
                                <input type="text" wire:model.live="phone"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                @error('phone')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label>Nationality</label>
                                <input type="text" wire:model.live="nationality"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror">
                                @error('nationality')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label>Resume</label>
                                <label
                                    class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">

                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.9A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>

                                    <p class="text-sm text-gray-500">
                                        <span class="font-semibold">Click to upload CV</span>
                                        or drag and drop
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        PDF, DOC, DOCX (Max 5MB)
                                    </p>

                                    <input type="file" wire:model="resume" accept=".pdf,.doc,.docx"
                                        class="hidden">
                                </label>
                                @error('resume')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                                <div wire:loading wire:target="resume" class="mt-2 text-blue-600">
                                    Uploading...
                                </div>

                                @if ($resume instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                    <div class="mt-2 text-sm text-green-600">
                                        Selected: {{ $resume->getClientOriginalName() }}
                                    </div>
                                @endif

                            </div>
                            <div class="mb-4">
                                <label>Cover Letter</label>
                                <textarea wire:model.live="cover_letter" rows="1"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror"></textarea>
                                @error('cover_letter')
                                    <span class="text-danger text-sm">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        @endif

                        <!-- Password -->
                        <div x-data="{ show: false }" class="mb-4">
                            <label class="block mb-2 text-sm font-medium">
                                Password
                            </label>

                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" wire:model.live="password"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror pr-12"
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
                                <span class="text-danger text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div x-data="{ showConfirm: false }" class="mb-6">
                            <label class="block mb-2 text-sm font-medium">
                                Confirm Password
                            </label>

                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'"
                                    wire:model.live="password_confirmation"
                                    class="w-full rounded-md border px-4 py-3 @error('email') border-red-500 @else border-gray-300 @enderror pr-12"
                                    placeholder="Confirm password">

                                <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute inset-y-0 right-0 flex items-center px-3">

                                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                            c4.477 0 8.268 2.943 9.542 7
                                            -1.274 4.057-5.065 7-9.542 7
                                            -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
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
                            @error('password_confirmation')
                                <span class="text-danger text-sm">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Button -->
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full bg-primary-800 hover:bg-primary-700 text-white py-3 rounded-md font-medium transition">
                            <span wire:loading.remove>
                                {{ $type === 'employer' ? 'Register as Employer' : 'Register as Candidate' }}
                            </span>
                            <span wire:loading>
                                Registering...
                            </span>
                        </button>
                        <div class="text-center mt-5">

                            <span class="text-gray-500">
                                Already have an account?
                            </span>

                            <a href="{{ route($type . '.login') }}"
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
