<?php

namespace App\Livewire;

use App\Models\Opening;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class JobDetails extends Component
{ 
    public $slug;
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
            'schemaData' => array_filter([
                $this->generateJobPostingSchema(),
                $this->generateFaqSchema(),
            ]),
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

            // Job Expiry Date
            'validThrough' => $this->job->created_at?->copy()->addMonth()->format('Y-m-d'),

            'employmentType' => $this->job->job_type?->getLabel(),

            // Salary Information
            'baseSalary' => filled($this->job->salary_range)
                    ? (function () {
                        [$min, $max] = array_pad(
                            explode('-', str_replace(' ', '', $this->job->salary_range)),
                            2,
                            null
                        );

                        return [
                            '@type' => 'MonetaryAmount',
                            'currency' => 'AED',
                            'value' => [
                                '@type' => 'QuantitativeValue',
                                'minValue' => $min,
                                'maxValue' => ($max ?: $min),
                                'unitText' => 'YEAR',
                            ],
                        ];
                    })()
                    : null,

            'hiringOrganization' => array_filter([
                '@type' => 'Organization',
                'name' => $this->job->employer->name ?? config('app.name'),
                'sameAs' => $this->job->employer->website ?? null,
                'logo' => $this->job->employer->logo
                    ? Storage::url($this->job->employer->logo)
                    : null,
            ], fn ($value) => filled($value)),

            'jobLocation' => array_filter([
                '@type' => 'Place',
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $addressParts[0] ?? null,
                    'addressLocality' => $addressParts[1] ?? ($this->job->location ?? null),
                    'addressRegion' => $addressParts[2] ?? null,
                    'postalCode' => $this->job->employer->postal_code ?? null,
                    'addressCountry' => $addressParts[3] ?? 'AE',
                ], fn ($value) => filled($value)),
            ], fn ($value) => filled($value)),

            'url' => route('jobs.show', $this->job->slug),
        ], fn ($value) => filled($value));
    }
    protected function generateFaqSchema(): ?array
    {
        $faqs = \App\Models\Faq::active()
            ->where('section', 'jobs')
            ->get();

        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => strtr($faq->question, [
                        '{category-name}' => $this->job->title,
                        '{place-name}' => $this->job->location,
                    ]),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags(
                            strtr($faq->answer, [
                                '{category-name}' => $this->job->title,
                                '{place-name}' => $this->job->location,
                            ])
                        ),
                    ],
                ];
            })->values()->all(),
        ];
    }
}
