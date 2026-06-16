<?php

namespace App\Livewire;

use App\Models\JobApplications;
use App\Models\Opening;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class JobDetails extends Component
{ public $slug;
    public $job;

    public function mount($slug)
    {
        $this->job = Opening::where('slug', $slug)->where('status', 1)->with('employer', 'job_category')->first();

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
}
