<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Auth\MustVerifyEmail;

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
    public $country = '';
    public $postal_code = '';

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
        $this->country = $employer->country;
        $this->postal_code = $employer->postal_code;
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
            'country' => ['nullable', 'string'],
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
            'country' => $this->country,
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

    <section class="mt-20 mb-50">
        <div class="container">
            
            {{-- Validation Notice alert --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                    <i class="fi-rr-exclamation me-2 text-xl"></i>
                    <div>
                        <strong>Validation Error:</strong> Please review and fix the marked fields in the form below.
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3">
                    <livewire:pages.employer.components.sidebar />
                </div>

                {{-- Content --}}
                <div class="col-lg-9">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-6">

                        <div class="mb-4 border-bottom pb-3">
                            <h2 class="text-2xl font-bold text-primary-800 mb-1">
                                Company Profile
                            </h2>
                            <p class="text-gray-600 mb-0">
                                Manage your company information, address, and upload logo.
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                                <i class="fi-rr-checkbox me-2 text-xl"></i>
                                <div>{{ session('success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form wire:submit="save">
                            <div class="row g-3">

                                {{-- Company Name --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Company Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" wire:model="name" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. Acme Corporation">
                                    @error('name')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Email Address --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" wire:model="email" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. hr@acme.com">
                                    @error('email')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Website --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Website
                                    </label>
                                    <input type="text" wire:model="website" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. https://www.acme.com">
                                    @error('website')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Phone Number --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Phone Number
                                    </label>
                                    <input type="text" wire:model="phone" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. +971 50 123 4567">
                                    @error('phone')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Description / TinyMCE --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-semibold text-gray-700">
                                        Description
                                    </label>
                                    <div wire:ignore x-data="profileFormEditor(@entangle('description'), '#description-editor', { height: 280, toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link' })">
                                        <textarea id="description-editor"></textarea>
                                    </div>
                                    @error('description')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Address --}}
                                <div class="col-md-12 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Address
                                    </label>
                                    <input type="text" wire:model="address" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. Office 101, Business Tower">
                                    @error('address')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- City --}}
                                <div class="col-md-4 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        City
                                    </label>
                                    <input type="text" wire:model="city" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. Dubai">
                                    @error('city')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- State --}}
                                <div class="col-md-4 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        State
                                    </label>
                                    <input type="text" wire:model="state" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. Dubai">
                                    @error('state')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Country --}}
                                <div class="col-md-4 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Country
                                    </label>
                                    <input type="text" wire:model="country" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. United Arab Emirates">
                                    @error('country')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Postal Code --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-semibold text-gray-700">
                                        Postal Code
                                    </label>
                                    <input type="text" wire:model="postal_code" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. 00000">
                                    @error('postal_code')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Logo Upload Section --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-semibold text-gray-700">
                                        Company Logo
                                    </label>
                                    <input type="file" wire:model="logo" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800">
                                    @error('logo')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror

                                    <div wire:loading wire:target="logo" class="mt-2 text-primary font-semibold">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Uploading logo...
                                    </div>

                                    @if ($employer->logo)
                                        <div class="mt-3 p-2 bg-gray-50 border rounded d-inline-block">
                                            <div class="text-xs text-gray-500 font-semibold mb-1">Current Logo</div>
                                            <img loading="lazy" src="{{ Storage::url($employer->logo) }}"
                                                alt="Company Logo" class="img-thumbnail" style="max-height:100px; object-fit: contain;">
                                        </div>
                                    @endif
                                </div>

                                {{-- Submit --}}
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-default hover-up d-inline-flex align-items-center gap-2 font-semibold shadow-sm" wire:loading.attr="disabled"
                                        wire:target="save,logo">
                                        <span wire:loading.remove wire:target="save">
                                            <i class="fi-rr-checkbox text-sm"></i>
                                            Save Changes
                                        </span>
                                        <span wire:loading wire:target="save">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Saving Changes...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
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
                    profileFormLoadScriptOnce('{{ asset("assets/js/tinymce/tinymce.min.js") }}'),
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
                        })();
                    },
                };
            }
        </script>
    @endonce
</div>
