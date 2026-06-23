<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    public $employer;
    public $logo;

    public function mount()
    {
        $this->employer = Auth::guard('employer')->user();
    }

    public function save()
    {
        $this->validate([
            'employer.name' => ['required', 'string', 'max:255'],
            'employer.email' => ['required', 'email', Rule::unique('employers', 'email')->ignore($this->employer->id)],
            'employer.website' => ['nullable', 'url'],
            'employer.phone' => ['nullable', 'string', 'max:20'],
            'employer.description' => ['nullable', 'string'],
            'employer.address' => ['nullable', 'string'],
            'employer.city' => ['nullable', 'string'],
            'employer.state' => ['nullable', 'string'],
            'employer.country' => ['nullable', 'string'],
            'employer.postal_code' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->logo) {
            $path = $this->logo->store('employers', 'public');
            $this->employer->logo = $path;
        }

        $this->employer->save();

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
                                    <label>Company Name</label>

                                    <input type="text" wire:model="employer.name" class="form-control">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Email Address</label>

                                    <input type="email" wire:model="employer.email" class="form-control">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Website</label>

                                    <input type="text" wire:model="employer.website" class="form-control">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Phone Number</label>

                                    <input type="text" wire:model="employer.phone" class="form-control">
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Description</label>

                                    <textarea wire:model="employer.description" rows="5" class="form-control"></textarea>
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Address</label>

                                    <input type="text" wire:model="employer.address" class="form-control">
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label>City</label>

                                    <input type="text" wire:model="employer.city" class="form-control">
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label>State</label>

                                    <input type="text" wire:model="employer.state" class="form-control">
                                </div>

                                <div class="col-md-4 mb-4">
                                    <label>Postal Code</label>

                                    <input type="text" wire:model="employer.postal_code" class="form-control">
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Company Logo</label>

                                    <input type="file" wire:model="logo" class="form-control">
                                </div>

                                <div class="col-md-12">

                                    <button type="submit" class="btn btn-default hover-up">

                                        Save Changes

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>
    </section>
</div>
