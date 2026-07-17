<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    public $employer;
    public $logo;
    public $name = '';
    public $email = '';
    public $website = '';
    public $phone = '';
    public $description = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public ?int $country_id = null;
    public $postal_code = '';
    public $nationalities = [];

    public function mount()
    {
        $employer = Auth::guard('employer')->user();
        $this->employer = $employer;

        $this->name = $employer->name;
        $this->email = $employer->email;
        $this->website = $employer->website;
        $this->phone = $employer->phone;
        $this->description = $employer->description;
        $this->address = $employer->address;
        $this->city = $employer->city;
        $this->state = $employer->state;
        $this->country_id = $employer->country_id;
        $this->postal_code = $employer->postal_code;

        $this->nationalities = rememberIfEnabled('active_nationalities', now()->addHours(12), function () {
            return \App\Models\Nationality::where('status', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    public function save()
    {
        $emailChanged = $this->email !== $this->employer->email;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('employers', 'email')->ignore($this->employer->id)],
            'website' => ['nullable', 'url'],
            'phone' => ['nullable', 'string', 'min:10', 'max:15'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'country_id' => ['nullable', 'exists:nationalities,id'],
            'postal_code' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $path = $this->employer->logo;

        if ($this->logo) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            $path = $this->logo->store('employers', 'public');
        }

        $this->employer->update([
            'name' => $this->name,
            'email' => $this->email,
            'website' => $this->website,
            'phone' => $this->phone,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country_id' => $this->country_id,
            'postal_code' => $this->postal_code,
            'logo' => $path,
            'email_verified_at' => $emailChanged ? null : $this->employer->email_verified_at,
        ]);

        if ($emailChanged) {
            $this->employer->refresh();

            $this->employer->sendEmailVerificationNotification();

            Auth::guard('employer')->logout();

            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('employer.verification.notice')->with('status', 'We have sent a verification link to your new email address.');
        }

        session()->flash('success', 'Profile updated successfully.');
    }
}; ?>

<div>
    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li>Employer Dashboard</li>
                <li>Profile</li>
            </ul>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Validation Notice alert --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-start gap-3">
                <i class="fi-rr-exclamation mt-0.5 text-lg"></i>
                <div>
                    <strong class="font-bold">Validation Error:</strong> Please review and fix the marked fields in the
                    form below.
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <livewire:pages.employer.components.sidebar />
            </div>

            {{-- Content --}}
            <div class="lg:col-span-3">
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 sm:p-8">
                    <div class="mb-8 border-b border-gray-100 pb-5">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Company Profile
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">
                            Manage your company information, address, and upload logo.
                        </p>
                    </div>

                    @if (session('success'))
                        <div
                            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
                            <i class="fi-rr-checkbox text-lg"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form wire:submit="save" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Company Name --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="name"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. Acme Corporation">
                                @error('name')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Email Address --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" wire:model="email"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. hr@acme.com">
                                @error('email')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Website --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Website</label>
                                <input type="text" wire:model="website"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. https://www.acme.com">
                                @error('website')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Phone Number --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                                <input type="text" wire:model="phone"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. +971 50 123 4567">
                                @error('phone')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <div wire:ignore x-data="profileFormEditor(@entangle('description'), '#description-editor', { height: 280, toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link' })">
                                    <textarea id="description-editor" class="w-full min-h-[200px] border border-gray-300 rounded-lg p-3"></textarea>
                                </div>
                                @error('description')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Address --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                                <input type="text" wire:model="address"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. Office 101, Business Tower">
                                @error('address')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- City --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                                <input type="text" wire:model="city"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. Dubai">
                                @error('city')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- State --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                                <input type="text" wire:model="state"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. Dubai">
                                @error('state')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Country --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Country</label>
                                <div wire:ignore>
                                    <select wire:model.live="country_id" id="profile-country-select"
                                        class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                        <option value="">Select country</option>
                                        @foreach ($nationalities as $nat)
                                            <option value="{{ $nat->id }}" @selected($nat->id == $country_id)>{{ $nat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('country_id')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Postal Code --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Postal Code</label>
                                <input type="text" wire:model="postal_code"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. 00000">
                                @error('postal_code')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Logo Upload --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Company Logo</label>
                                <input type="file" wire:model="logo"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 file:cursor-pointer hover:file:bg-primary-100 transition border border-gray-200 rounded-lg p-1 bg-gray-50">
                                @error('logo')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror

                                <div wire:loading wire:target="logo" class="mt-2 flex items-center gap-2">
                                    <span
                                        class="w-4 h-4 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></span>
                                    <small class="text-primary-600 font-medium">Uploading logo...</small>
                                </div>

                                @if ($employer->logo)
                                    <div class="mt-4 p-4 bg-gray-50 border border-gray-100 rounded-lg inline-block">
                                        <div class="text-xs text-gray-500 font-bold mb-2 uppercase tracking-wider">
                                            Current Logo</div>
                                        <img loading="lazy" src="{{ Storage::url($employer->logo) }}"
                                            alt="Company Logo" class="rounded-lg shadow-sm border border-gray-200"
                                            style="max-height:80px; object-fit: contain;">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg shadow-sm text-white bg-primary-800 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="save,logo">
                                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                                    <i class="fi-rr-checkbox text-base"></i>
                                    Save Changes
                                </span>
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <span
                                        class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @once
        <script>
            function profileFormLoadScriptOnce(src) {
                return new Promise((resolve, reject) => {
                    if (document.querySelector(`script[src="${src}"]`)) {
                        resolve();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = src;
                    script.onload = () => resolve();
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            window.profileFormDependencies = window.profileFormDependencies || (async function() {
                await Promise.all([
                    window.tinymce ?
                    Promise.resolve() :
                    profileFormLoadScriptOnce('{{ asset('assets/js/tinymce/tinymce.min.js') }}'),
                ]);
            })();

            function profileFormEditor(initialValue, selector, options) {
                options = options || {};

                return {
                    value: initialValue,
                    editor: null,
                    init() {
                        (async () => {
                            await window.profileFormDependencies;

                            tinymce.init({
                                selector: selector,
                                base_url: '/assets/js/tinymce',
                                suffix: '.min',
                                height: options.height || 240,
                                menubar: false,
                                plugins: 'lists link',
                                toolbar: options.toolbar || 'bold italic | bullist numlist | link',
                                branding: false,
                                setup: (editor) => {
                                    this.editor = editor;

                                    editor.on('init', () => {
                                        editor.setContent(this.value || '');
                                    });

                                    editor.on('change keyup undo redo', () => {
                                        this.value = editor.getContent();
                                    });
                                },
                            });

                            this.$watch('value', (newValue) => {
                                if (this.editor && this.editor.getContent() !== (newValue || '')) {
                                    this.editor.setContent(newValue || '');
                                }
                            });
                        })
                        ();
                    },
                };
            }
        </script>
    @endonce
</div>
    @push('js')
        <script>
            function employerProfileSelect2() {
                if ($('#profile-country-select').length) {
                    $('#profile-country-select').select2({
                        placeholder: 'Select country',
                        allowClear: true,
                        width: '100%',
                    }).on('change', function() {
                        @this.set('country_id', $(this).val() || null);
                    });
                }
            }

            document.addEventListener('livewire:initialized', employerProfileSelect2);
        </script>
    @endpush

