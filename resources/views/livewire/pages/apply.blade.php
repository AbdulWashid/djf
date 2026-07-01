<?php

use Livewire\Volt\Component;
use App\Models\JobApplications;
use App\Models\Opening;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    public $slug;
    public $job;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $nationality = '';
    public $cover_letter = '';
    public $cv;
    public $candidate;

    public function mount($slug)
    {
        $candidate = Auth::guard('candidate')->user();
        $this->candidate = $candidate;

        $this->first_name = $candidate->first_name;
        $this->last_name = $candidate->last_name;
        $this->email = $candidate->email;
        $this->phone = $candidate->phone;
        $this->nationality = $candidate->nationality;
        $this->cover_letter = $candidate->cover_letter;

        $this->job = Opening::where('slug', $slug)->where('status', 1)->with('employer', 'job_category')->first();

        abort_if(!$this->job, 404);

        view()->share('pageType', 'job_posting');

        view()->share('pageTitle', $this->job->meta_title ?? 'Jobs in ' . $this->job->location . ' | ' . $this->job->title . ' | Apply Now - Dubaijobfinder');

        view()->share('pageDescription', $this->job->meta_description ?? 'Find the latest ' . $this->job->title . ' jobs in ' . $this->job->location . '. Apply online for urgent vacancies and career opportunities on Dubaijobfinder.');

        view()->share('metaKeywords', $this->job->meta_keywords);

        view()->share('ogTags', $this->job->og_tags);

        view()->share('twitterTags', $this->job->twitter_tags);

        view()->share('schemaData', [$this->generateJobPostingSchema()]);
    }

    protected function rules()
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:120'],
            'last_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'nationality' => ['required', 'string', 'max:120'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function submit()
    {
        $this->validate();

        if (auth('candidate')->check()) {
            $exists = JobApplications::where('opening_id', $this->job->id)
                ->where('candidate_id', auth('candidate')->id())
                ->exists();

            if ($exists) {
                $this->addError('application', 'You have already applied for this job.');
                return;
            }
        }

        if ($this->cv) {
            $resumePath = $this->cv->store('job-applications/resumes', 'public');
        } else {
            $resumePath = $this->candidate->resume_path;
        }
        $data = JobApplications::create([
            'candidate_id' => $this->candidate->id,
            'opening_id' => $this->job->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'nationality' => $this->nationality,
            'cover_letter' => $this->cover_letter,
            'resume_path' => $resumePath,
            'status' => 'pending',
        ]);

        return redirect()->route('jobs.apply.thankyou', $this->job->slug)->with('application_id', $data->id);
    }

    protected function generateJobPostingSchema(): array
    {
        $addressParts = array_map('trim', explode(',', (string) ($this->job->employer->address ?? '')));

        return array_filter(
            [
                '@context' => 'https://schema.org',
                '@type' => 'JobPosting',
                'title' => $this->job->title,
                'description' => strip_tags($this->job->description ?? ''),
                'datePosted' => $this->job->created_at?->toIso8601String(),
                'employmentType' => $this->job->job_type?->getLabel(),
                'hiringOrganization' => array_filter(
                    [
                        '@type' => 'Organization',
                        'name' => $this->job->employer->name ?? config('app.name'),
                        'sameAs' => $this->job->employer->website ?? null,
                        'logo' => $this->job->employer->logo ? Storage::url($this->job->employer->logo) : null,
                    ],
                    fn($value) => filled($value),
                ),
                'jobLocation' => array_filter(
                    [
                        '@type' => 'Place',
                        'address' => array_filter(
                            [
                                '@type' => 'PostalAddress',
                                'streetAddress' => $addressParts[0] ?? null,
                                'addressLocality' => $addressParts[1] ?? ($this->job->location ?? null),
                                'addressRegion' => $addressParts[2] ?? null,
                                'addressCountry' => $addressParts[3] ?? null,
                            ],
                            fn($value) => filled($value),
                        ),
                    ],
                    fn($value) => filled($value),
                ),
                'url' => route('jobs.show', $this->job->slug),
            ],
            fn($value) => filled($value),
        );
    }
}; ?>

<div>
    <div class="container py-5">
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
        @if (session()->has('application'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('application') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div class="row">
            <!-- Sidebar: Employer & Job Summary -->
            <div class="col-lg-4 mb-4">
                <!-- Employer Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <img loading="lazy"
                                src="{{ $job->employer->logo ? Storage::url($job->employer->logo) : 'https://placehold.co/100x100?text=' . urlencode($job->employer->name) }}"
                                alt="{{ $job->employer->name }}" width="60" height="60"
                                class="rounded border me-3">
                            <div>
                                <h5 class="mb-0">{{ $job->employer->name }}</h5>
                                <small class="text-muted">Employer</small>
                            </div>
                        </div>

                        <div class="small text-muted mb-3">
                            {!! $job->employer->description !!}
                        </div>

                        <hr class="my-3">

                        <div class="d-grid gap-2">
                            @if ($job->employer->email)
                                <div class="d-flex align-items-center small">
                                    <i class="fi-rr-envelope me-2 text-primary"></i> {{ $job->employer->email }}
                                </div>
                            @endif
                            @if ($job->employer->phone)
                                <div class="d-flex align-items-center small">
                                    <i class="fi-rr-phone-call me-2 text-primary"></i> {{ $job->employer->phone }}
                                </div>
                            @endif
                            @if ($job->employer->address)
                                <div class="d-flex align-items-start small">
                                    <i class="fi-rr-marker me-2 text-primary mt-1"></i>
                                    <span>{{ collect([$job->employer->address, $job->employer->city, $job->employer->country])->filter()->implode(', ') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Job Summary Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small">Job Overview</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="fi-rr-map-marker me-2"></i>Location</span>
                            <span class="fw-semibold">{{ $job->location }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="fi-rr-briefcase me-2"></i>Type</span>
                            <span class="fw-semibold">{{ $job->job_type->getLabel() }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="fi-rr-money me-2"></i>Salary</span>
                            <span class="fw-semibold text-success">{{ $job->salary_range }} AED</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Area -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4 fw-bold">Apply for {{ $job->title }}</h3>
                        <p class="text-muted mb-4">Complete the form below to submit your application directly to the
                            hiring
                            team.</p>

                        <form wire:submit="submit" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">First Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" wire:model.blur="first_name"
                                        value="{{ $this->candidate->first_name }}"
                                        class="form-control form-control-lg @error('first_name') is-invalid @enderror"
                                        required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Last Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" wire:model.blur="last_name"
                                        value="{{ $this->candidate->last_name }}"
                                        class="form-control form-control-lg @error('last_name') is-invalid @enderror"
                                        required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" wire:model.blur="email" value="{{ $this->candidate->email }}"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" wire:model.blur="phone" value="{{ $this->candidate->phone }}"
                                        class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                        required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-medium">Nationality <span
                                            class="text-danger">*</span></label>
                                    <input type="text" wire:model.blur="nationality"
                                        value="{{ $this->candidate->nationality }}"
                                        class="form-control form-control-lg @error('nationality') is-invalid @enderror"
                                        required>
                                </div>

                                <!-- Optimized CV Upload Section -->
                                @if ($candidate->resume_path)
                                    <div class="mb-3 p-3 border rounded bg-light">

                                        <div class="d-flex justify-content-between align-items-center">

                                            <div>
                                                <strong>Current Resume</strong><br>
                                                <small class="text-muted">
                                                    {{ basename($candidate->resume_path) }}
                                                </small>
                                            </div>

                                            <a href="{{ Storage::url($candidate->resume_path) }}" target="_blank"
                                                class="btn btn-sm">
                                                <i class="fi-rr-download me-1"></i>
                                            </a>

                                        </div>

                                    </div>
                                @endif
                                <div class="col-12 mb-4">
                                    <label class="form-label fw-bold text-dark mb-2">
                                        Upload New Resume (Optional)
                                    </label>

                                    <p class="text-muted small">
                                        Leave this empty if you want to use your existing resume.
                                    </p>

                                    <label for="cv-upload"
                                        class="d-flex flex-column align-items-center justify-content-center w-100 p-4 border border-2 border-dashed rounded-3 bg-light cursor-pointer hover-border-primary transition-all">
                                        <!-- Default state -->
                                        <div wire:loading.remove wire:target="cv" class="text-center">
                                            <i class="fi-rr-cloud-upload fs-1 text-primary mb-2"></i>
                                            <span class="fw-medium text-muted d-block">Click to upload or drag and
                                                drop</span>
                                            <span class="text-xs text-secondary mt-1">PDF, DOC, DOCX (Max 5MB)</span>
                                        </div>

                                        <!-- Loading state for CV -->
                                        <div wire:loading wire:target="cv" class="text-center text-primary">
                                            <i class="fa fa-spinner fa-spin fs-1 mb-2"></i>
                                            <span class="fw-bold d-block">Uploading your file...</span>
                                        </div>

                                        <input type="file" id="cv-upload" wire:model="cv" class="d-none"
                                            accept=".pdf,.doc,.docx">
                                    </label>

                                    @if ($cv)
                                        <div class="mt-2 text-success small"><i class="fi-rr-check-circle me-1"></i>
                                            File
                                            selected: {{ $cv->getClientOriginalName() }}</div>
                                    @endif
                                    @error('cv')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-4">
                                    <label class="form-label fw-medium">Cover Letter</label>
                                    <textarea wire:model.blur="cover_letter" rows="5" class="form-control form-control-lg"
                                        value="{{ $this->candidate->cover_letter }}" placeholder="Tell us why you are a great fit..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow-sm"
                                wire:loading.attr="disabled" wire:target="submit">
                                <span wire:loading.remove wire:target="submit">
                                    <i class="fi-rr-paper-plane me-2"></i> Submit Application
                                </span>
                                <span wire:loading wire:target="submit">
                                    <i class="fa fa-spinner fa-spin me-2"></i> Submitting...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
