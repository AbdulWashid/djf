<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\Nationality;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    public $candidate;

    public $resume;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public ?int $nationality_id = null;
    public $cover_letter = '';
    public $nationalities = [];

    public function mount()
    {
        $candidate = Auth::guard('candidate')->user();

        $this->candidate = $candidate;

        $this->first_name = $candidate->first_name;
        $this->last_name = $candidate->last_name;
        $this->email = $candidate->email;
        $this->phone = $candidate->phone;
        $this->nationality_id = $candidate->nationality_id;
        $this->cover_letter = $candidate->cover_letter;

        $this->nationalities = rememberIfEnabled('active_nationalities', now()->addHours(12), function () {
            return Nationality::where('status', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }
    public function save()
    {
        $emailChanged = $this->email !== $this->candidate->email;

        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('candidates', 'email')->ignore($this->candidate->id)],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'nationality_id' => ['nullable', 'exists:nationalities,id'],
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
            'nationality_id' => $this->nationality_id,
            'cover_letter' => $this->cover_letter,
            'resume_path' => $resumePath,
            'email_verified_at' => $emailChanged ? null : $this->candidate->email_verified_at,
        ]);

        if ($emailChanged) {
            $this->candidate->refresh();

            $this->candidate->sendEmailVerificationNotification();

            Auth::guard('candidate')->logout();

            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('candidate.verification.notice')->with('status', 'We have sent a verification link to your new email address.');
        }

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

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-start gap-3">
                <i class="fi-rr-exclamation mt-0.5 text-lg"></i>
                <div>
                    <h5 class="font-bold mb-1">Please correct the following errors:</h5>
                    <ul class="list-disc list-inside text-sm space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <livewire:pages.candidate.components.sidebar />
            </div>

            {{-- Content --}}
            <div class="lg:col-span-3">
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 sm:p-8">
                    <div class="mb-8 border-b border-gray-100 pb-5">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Candidate Profile
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">
                            Manage your personal information, contact details, and resume.
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
                            {{-- First Name --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">First Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.live="first_name"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                @error('first_name')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Last Name --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.live="last_name"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                @error('last_name')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Email Address --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span
                                        class="text-red-500">*</span></label>
                                <input type="email" wire:model.live="email"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                @error('email')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Phone Number --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.live="phone"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                @error('phone')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Nationality --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nationality</label>
                                <div wire:ignore>
                                    <select wire:model.live="nationality_id" id="profile-nationality-select"
                                        class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                        <option value="">Select nationality</option>
                                        @foreach ($nationalities as $nat)
                                            <option value="{{ $nat->id }}" @selected($nat->id == $nationality_id)>{{ $nat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('nationality_id')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Cover Letter --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Cover Letter</label>
                                <textarea wire:model.live="cover_letter" rows="6"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"></textarea>
                                @error('cover_letter')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Resume Upload --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Resume (PDF, DOC,
                                    DOCX)</label>
                                <div class="flex items-center gap-4">
                                    <input type="file" wire:model="resume"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 file:cursor-pointer hover:file:bg-primary-100 transition border border-gray-200 rounded-lg p-1 bg-gray-50">
                                </div>
                                @error('resume')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror

                                <div wire:loading wire:target="resume" class="mt-2 flex items-center gap-2">
                                    <span
                                        class="w-4 h-4 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></span>
                                    <small class="text-primary-600 font-medium">Uploading resume...</small>
                                </div>

                                @if ($candidate->resume_path)
                                    <div class="mt-3 flex items-center gap-2 text-sm">
                                        <i class="fi-rr-document text-primary-600"></i>
                                        <a href="{{ Storage::url($candidate->resume_path) }}" target="_blank"
                                            class="text-primary-600 hover:text-primary-800 font-semibold hover:underline">
                                            View Current Resume
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg shadow-sm text-white bg-primary-800 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="save,resume">
                                <span wire:loading.remove wire:target="save">
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
</div>
    @push('js')
        <script>
            function candidateProfileSelect2() {
                if ($('#profile-nationality-select').length) {
                    $('#profile-nationality-select').select2({
                        placeholder: 'Select nationality',
                        allowClear: true,
                        width: '100%',
                    }).on('change', function() {
                        @this.set('nationality_id', $(this).val() || null);
                    });
                }
            }

            document.addEventListener('livewire:initialized', candidateProfileSelect2);
        </script>
    @endpush

