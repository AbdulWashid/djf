<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    public $candidate;
    public $resume;

    public function mount()
    {
        $this->candidate = Auth::guard('candidate')->user();
    }

    public function save()
    {
        $this->validate([
            'candidate.first_name' => ['required', 'string', 'max:255'],
            'candidate.last_name' => ['required', 'string', 'max:255'],
            'candidate.email' => ['required', 'email', Rule::unique('candidates', 'email')->ignore($this->candidate->id)],
            'candidate.phone' => ['required', 'string', 'max:20'],
            'candidate.nationality' => ['nullable', 'string', 'max:255'],
            'candidate.cover_letter' => ['nullable', 'string'],
            'resume' => ['nullable', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        if ($this->resume) {
            $this->candidate->resume_path = $this->resume->store('resumes', 'public');
        }

        $this->candidate->save();

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
                                    <label>First Name</label>
                                    <input type="text" wire:model="candidate.first_name" class="form-control">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Last Name</label>
                                    <input type="text" wire:model="candidate.last_name" class="form-control">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Email Address</label>
                                    <input type="email" wire:model="candidate.email" class="form-control">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Phone Number</label>
                                    <input type="text" wire:model="candidate.phone" class="form-control">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label>Nationality</label>
                                    <input type="text" wire:model="candidate.nationality" class="form-control">
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Cover Letter</label>
                                    <textarea wire:model="candidate.cover_letter" rows="6" class="form-control"></textarea>
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label>Resume (PDF, DOC, DOCX)</label>

                                    <input type="file" wire:model="resume" class="form-control">

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
