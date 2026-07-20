<?php

use Livewire\Volt\Component;
use App\Models\JobApplications;
use App\Models\Opening;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

new #[Layout('components.frontend.main')] class extends Component {
    use WithFileUploads;

    public $slug;
    public $job;
    public $application;

    public function mount($slug = 'data-entry-specialist')
    {
        $this->job = Opening::active()
            ->where('slug', $slug)
            ->with(['employer', 'job_category', 'location'])
            ->first();
        abort_if(!$this->job, 404);

        if (session()->has('application_id')) {
            $applicationId = session('application_id');
            // $this->application = JobApplications::findOrFail(36);
            $this->application = JobApplications::findOrFail($applicationId);
        } else {
            return redirect()->route('jobs.show', $slug);
        }

        view()->share('pageType', 'job_posting');

        view()->share('pageTitle', $this->job->meta_title ?? 'Jobs in ' . $this->job->location?->name . ' | ' . $this->job->title . ' | Apply Now - Dubaijobfinder');
        view()->share('pageDescription', $this->job->meta_description ?? 'Find the latest ' . $this->job->title . ' jobs in ' . $this->job->location?->name . '. Apply online for urgent vacancies and career opportunities on Dubaijobfinder.');
        view()->share('metaKeywords', $this->job->meta_keywords);

        view()->share('ogTags', $this->job->og_tags);

        view()->share('twitterTags', $this->job->twitter_tags);

        view()->share('schemaData', [$this->generateJobPostingSchema()]);
    }

    protected function generateJobPostingSchema(): array
    {
        $addressParts = array_map('trim', explode(',', (string) ($this->job->employer->address ?? '')));

        $streetAddress = filled($addressParts[0] ?? null) ? $addressParts[0] : (filled($this->job->employer->address ?? null) ? $this->job->employer->address : ($this->job->location?->name ?? 'Dubai'));
        $addressLocality = filled($addressParts[1] ?? null) ? $addressParts[1] : ($this->job->location?->name ?? ($this->job->employer->city ?? 'Dubai'));
        $addressRegion = filled($addressParts[2] ?? null) ? $addressParts[2] : ($this->job->employer->state ?? 'Dubai');
        $addressCountry = filled($addressParts[3] ?? null) ? $addressParts[3] : 'AE';
        $postalCode = $this->job->employer->postal_code ?? null;

        $address = [
            '@type' => 'PostalAddress',
            'streetAddress' => $streetAddress,
            'addressLocality' => $addressLocality,
            'addressRegion' => $addressRegion,
            'addressCountry' => $addressCountry,
        ];

        if (filled($postalCode)) {
            $address['postalCode'] = $postalCode;
        }

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
                'jobLocation' => [
                    '@type' => 'Place',
                    'address' => $address,
                ],
                'url' => route('jobs.show', $this->job->slug),
            ],
            fn($value) => filled($value),
        );
    }
}; ?>

<div>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 text-center p-4 p-md-5">

                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle"
                            style="width: 80px; height: 80px;">
                            <svg width="80px" height="80px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#00f925"
                                    d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zm-55.808 536.384-99.52-99.584a38.4 38.4 0 1 0-54.336 54.336l126.72 126.72a38.272 38.272 0 0 0 54.336 0l262.4-262.464a38.4 38.4 0 1 0-54.272-54.336L456.192 600.384z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <h2 class="fw-bold mb-3">Application Received!</h2>
                    <p class="text-muted fs-5">
                        Hello <strong>{{ $application->first_name }}</strong>, your application for
                        <span class="text-dark fw-semibold">{{ $job->title }}</span> has been successfully submitted.
                    </p>

                    <div class="bg-light p-3 rounded-3 my-4 text-start">
                        <p class="mb-1 text-muted small text-uppercase fw-bold">Next Steps</p>
                        <p class="mb-0 small text-secondary">
                            Our recruitment team is currently reviewing your profile. If your skills and experience
                            match
                            the requirements, we will reach out to you directly at
                            <strong>{{ $application->email }}</strong>.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-3">
                        <a href="{{ route('jobs') }}" class="btn btn-outline-primary btn-lg px-4 rounded-pill">
                            Browse More Jobs
                        </a>
                        <a href="{{ route('jobs.show', $job->slug) }}" class="btn btn-primary btn-lg px-4 rounded-pill">
                            View Job Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
