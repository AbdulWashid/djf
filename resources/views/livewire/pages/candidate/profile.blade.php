<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    // public $candidate;
    // public $resume;

    // public function mount()
    // {
    //     $candidate = Auth::guard('candidate')->user();
    //     $this->candidate = $candidate;
    // }
    public $candidate;

    public $resume;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $nationality = '';
    public $cover_letter = '';

    public function mount()
    {
        $candidate = Auth::guard('candidate')->user();

        $this->candidate = $candidate;

        $this->first_name = $candidate->first_name;
        $this->last_name = $candidate->last_name;
        $this->email = $candidate->email;
        $this->phone = $candidate->phone;
        $this->nationality = $candidate->nationality;
        $this->cover_letter = $candidate->cover_letter;
    }
    public function save()
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('candidates', 'email')->ignore($this->candidate->id)],
            'phone' => ['required', 'string', 'max:20'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'cover_letter' => ['nullable', 'string'],
            'resume' => ['nullable', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $resumePath = $this->candidate->resume_path;

        if ($this->resume) {
            if ($resumePath && Storage::disk('public')->exists($resumePath)) {
                Storage::disk('public')->delete($resumePath);
            }

            $resumePath = $this->resume->store('resumes', 'public');
        }

        $this->candidate->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'nationality' => $this->nationality,
            'cover_letter' => $this->cover_letter,
            'resume_path' => $resumePath,
        ]);

        session()->flash('success', 'Profile updated successfully.');
    }
};
?>

<div>
    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li>Candidate Dashboard</li>
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
                    <livewire:pages.candidate.components.sidebar />
                </div>

                {{-- Content --}}
                <div class="col-lg-9">

                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-5">

                        <div class="mb-5">
                            <h2 class="text-2xl font-bold text-primary-800">
                                Candidate Profile
                            </h2>

                            <p class="text-gray-600">
                                Manage your personal information and resume.
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
                                    <label>First Name <span class="text-danger">*</span></label>

                                    <input type="text" wire:model.live="first_name" class="form-control">

                                    @error('first_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Last Name <span class="text-danger">*</span></label>

                                    <input type="text" wire:model.live="last_name" class="form-control">

                                    @error('last_name')
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
                                    <label>Phone Number <span class="text-danger">*</span></label>

                                    <input type="text" wire:model.live="phone" class="form-control">

                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Nationality</label>

                                    <input type="text" wire:model.live="nationality" class="form-control">

                                    @error('nationality')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Cover Letter</label>

                                    <textarea wire:model.live="cover_letter" rows="6" class="form-control"></textarea>

                                    @error('cover_letter')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Resume (PDF, DOC, DOCX)</label>

                                    <input type="file" wire:model="resume" class="form-control">

                                    @error('resume')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    <div wire:loading wire:target="resume" class="mt-2">
                                        <small class="text-primary">Uploading resume...</small>
                                    </div>

                                    @if ($candidate->resume_path)
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($candidate->resume_path) }}" target="_blank"
                                                class="text-primary">
                                                View Current Resume
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-default hover-up" wire:loading.attr="disabled"
                                        wire:target="save,resume">

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
        </div>
    </section>
</div>
