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

        // $this->employer->save();

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
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3">
                    <livewire:pages.employer.components.sidebar />
                </div>

                {{-- Content --}}
                <div class="col-lg-9">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-5">

                        <div class="mb-5">
                            <h2 class="text-2xl font-bold text-primary-800">
                                Company Profile
                            </h2>

                            <p class="text-gray-600">
                                Manage your company information and contact details.
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form wire:submit="save">

                            <div class="row">

                                <div class="col-md-6 mb-4">
                                    <label>Company Name <span class="text-danger">*</span></label>

                                    <input type="text" wire:model.live="name" class="form-control">

                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Email Address <span class="text-danger">*</span></label>

                                    <input type="email" wire:model.live="email" class="form-control">

                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Website</label>

                                    <input type="text" wire:model.live="website" class="form-control">

                                    @error('website')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Phone Number</label>

                                    <input type="text" wire:model.live="phone" class="form-control">

                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Description</label>

                                    <textarea wire:model.live="description" rows="5" class="form-control"></textarea>

                                    @error('description')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Address</label>

                                    <input type="text" wire:model.live="address" class="form-control">

                                    @error('address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label>City</label>

                                    <input type="text" wire:model.live="city" class="form-control">

                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label>State</label>

                                    <input type="text" wire:model.live="state" class="form-control">

                                    @error('state')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label>Country</label>

                                    <input type="text" wire:model.live="country" class="form-control">

                                    @error('country')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label>Postal Code</label>

                                    <input type="text" wire:model.live="postal_code" class="form-control">

                                    @error('postal_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Company Logo</label>

                                    <input type="file" wire:model="logo" class="form-control">

                                    @error('logo')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    <div wire:loading wire:target="logo" class="mt-2">
                                        <small class="text-primary">Uploading logo...</small>
                                    </div>

                                    @if ($employer->logo)
                                        <div class="mt-2">
                                            <img loading="lazy" src="{{ Storage::url($employer->logo) }}"
                                                alt="Company Logo" class="img-thumbnail" style="max-height:100px;">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-default hover-up" wire:loading.attr="disabled"
                                        wire:target="save,logo">

                                        <span wire:loading.remove wire:target="save">
                                            Save Changes
                                        </span>

                                        <span wire:loading wire:target="save">
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </section>
</div>
