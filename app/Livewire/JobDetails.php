<?php

namespace App\Livewire;

use App\Models\JobApplications;
use App\Models\Opening;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class JobDetails extends Component
{
    use WithFileUploads;
    public $slug;
    public $job;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $cv = null;

    public bool $showApplyModal = false;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $nationality = '';
    public ?string $cover_letter = null;

    public bool $applySuccess = false;
    public ?string $applySuccessMessage = null;

    protected $rules = [
        'first_name' => 'required|min:2|max:120',
        'last_name' => 'required|min:2|max:120',
        'email' => 'required|email',
        'phone' => 'required|max:50',
        'nationality' => 'required|max:120',
        'cover_letter' => 'nullable|max:2000',
        'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
    ];

    public function mount($slug)
    {
        $this->job = Opening::where('slug', $slug)->where('status', 1)->with('employer','job_category')->first();
        
        if (!$this->job) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.job-details')->layout('components.frontend.main', [
            'pageType' => 'job_posting',
            'pageTitle' => $this->job->meta_title ?? 'Jobs in '. $this->job->location .' | '. $this->job->title.' | Apply Now - Dubaijobfinder',
            'pageDescription' => $this->job->meta_description ?? 'Find the latest '. $this->job->title.' jobs in '. $this->job->location .'. Apply online for urgent vacancies and career opportunities on Dubaijobfinder.',
            'metaKeywords' => $this->job->meta_keywords,
            'ogTags' => $this->job->og_tags,
            'twitterTags' => $this->job->twitter_tags,
            'schemaData' => [$this->generateJobPostingSchema()],
        ]);
    }

    protected function generateJobPostingSchema(): array
    {
        $addressParts = array_map('trim', explode(',', (string) ($this->job->employer->address ?? '')));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $this->job->title,
            'description' => strip_tags($this->job->description ?? ''),
            'datePosted' => $this->job->created_at?->toIso8601String(),
            'employmentType' => $this->job->job_type?->getLabel(),
            'hiringOrganization' => array_filter([
                '@type' => 'Organization',
                'name' => $this->job->employer->name ?? config('app.name'),
                'sameAs' => $this->job->employer->website ?? null,
                'logo' => $this->job->employer->logo ? Storage::url($this->job->employer->logo) : null,
            ], fn ($value) => filled($value)),
            'jobLocation' => array_filter([
                '@type' => 'Place',
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $addressParts[0] ?? null,
                    'addressLocality' => $addressParts[1] ?? ($this->job->location ?? null),
                    'addressRegion' => $addressParts[2] ?? null,
                    'addressCountry' => $addressParts[3] ?? null,
                ], fn ($value) => filled($value)),
            ], fn ($value) => filled($value)),
            'url' => route('jobs.show', $this->job->slug),
        ], fn ($value) => filled($value));
    }

    public function openApplyModal(): void
    {
        $this->resetValidation();
        $this->applySuccess = false;
        $this->applySuccessMessage = null;
        $this->showApplyModal = true;
    }

    public function closeApplyModal(): void
    {
        $this->showApplyModal = false;
    }

    public function submitApplication(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:120'],
            'last_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'nationality' => ['required', 'string', 'max:120'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB
        ]);

        $resumePath = $this->cv->store('job-applications/resumes', 'public');

        JobApplications::create([
            'opening_id' => $this->job->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'cover_letter' => $validated['cover_letter'] ?? null,
            'resume_path' => $resumePath,
            'nationality' => $validated['nationality'],
            'status' => 'pending',
        ]);

        $this->reset(['first_name', 'last_name', 'email', 'phone', 'nationality', 'cover_letter', 'cv']);

        $this->showApplyModal = false;

        $this->applySuccess = true;
        $this->applySuccessMessage = 'Your application has been submitted successfully.';
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }
}
