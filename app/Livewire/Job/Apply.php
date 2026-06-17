<?php

namespace App\Livewire\Job;

use App\Models\JobApplications;
use App\Models\Opening;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Apply extends Component
{ 
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

    public function mount($slug)
    {
        $this->job = Opening::where('slug', $slug)
            ->where('status', 1)
            ->with('employer', 'job_category')
            ->first();

        abort_if(!$this->job, 404);
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
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function submit()
    {
        $this->validate();

        $resumePath = $this->cv->store(
            'job-applications/resumes',
            'public'
        );

        $data = JobApplications::create([
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

        return redirect()->route(
            'jobs.apply.thankyou',
            $this->job->slug
        )->with('application_id', $data->id);
    }

    public function render()
    {
        return view('livewire.job.apply')->layout('components.frontend.main',
        [
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
}
