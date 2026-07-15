<?php

use Livewire\Volt\Component;
use App\Models\Opening;
use App\Models\Faq;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use App\Models\JobApplications;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.frontend.main')] class extends Component {
    public $slug;
    public $job;
    public bool $alreadyApplied = false;

    public function mount($slug)
    {
        $this->job = rememberIfEnabled(
            "job:{$slug}",
            now()->addMinutes(30),
            fn() => Opening::select(['id', 'slug', 'title', 'description', 'responsibilities', 'skills', 'benefits', 'salary_range', 'location_id', 'job_type', 'employer_id', 'job_category_id', 'meta_title', 'meta_description', 'meta_keywords', 'twitter_tags', 'og_tags', 'created_at'])
                ->where('slug', $slug)
                ->active()
                ->with(['employer', 'job_category', 'location'])
                ->first(),
        );
        if (!$this->job) {
            abort(404);
        }

        if (auth('candidate')->check()) {
            $this->alreadyApplied = JobApplications::where('opening_id', $this->job->id)
                ->where('candidate_id', auth('candidate')->id())
                ->exists();
        }

        view()->share('pageType', 'job_posting');

        view()->share('pageTitle', $this->job->meta_title ?? 'Jobs in ' . $this->job->location?->name . ' | ' . $this->job->title . ' | Apply Now - Dubaijobfinder');

        view()->share('pageDescription', $this->job->meta_description ?? 'Find the latest ' . $this->job->title . ' jobs in ' . $this->job->location?->name . '. Apply online for urgent vacancies and career opportunities on Dubaijobfinder.');

        view()->share('metaKeywords', $this->job->meta_keywords);

        view()->share('ogTags', $this->job->og_tags);

        view()->share('twitterTags', $this->job->twitter_tags);

        view()->share('schemaData', array_filter([$this->generateJobPostingSchema(), $this->generateFaqSchema()]));
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

                // Job Expiry Date
                'validThrough' => $this->job->created_at?->copy()->addMonth()->format('Y-m-d'),

                'employmentType' => $this->job->job_type?->getLabel(),

                // Salary Information
                'baseSalary' => filled($this->job->salary_range)
                    ? (function () {
                        [$min, $max] = array_pad(explode('-', str_replace(' ', '', $this->job->salary_range)), 2, null);

                        return [
                            '@type' => 'MonetaryAmount',
                            'currency' => 'AED',
                            'value' => [
                                '@type' => 'QuantitativeValue',
                                'minValue' => $min,
                                'maxValue' => $max ?: $min,
                                'unitText' => 'YEAR',
                            ],
                        ];
                    })()
                    : null,

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
                                'addressLocality' => $addressParts[1] ?? ($this->job->location?->name ?? null),
                                'addressRegion' => $addressParts[2] ?? null,
                                'postalCode' => $this->job->employer->postal_code ?? null,
                                'addressCountry' => $addressParts[3] ?? 'AE',
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
    protected function generateFaqSchema(): ?array
    {
        $faqs = rememberIfEnabled('job_faqs', now()->addHours(12), fn() => Faq::active()->where('section', 'jobs')->get());
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs
                ->map(function ($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => strtr($faq->question, [
                            '{category-name}' => $this->job->title,
                            '{place-name}' => $this->job->location?->name,
                        ]),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags(
                                strtr($faq->answer, [
                                    '{category-name}' => $this->job->title,
                                    '{place-name}' => $this->job->location?->name,
                                ]),
                            ),
                        ],
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}; ?>

<div>
    <section class="section-box">
        <div class="box-head-single">
            <div class="container">
                <h1 style="font-size: 36px;">{{ $job->title }}</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('jobs') }}">Jobs listing</a></li>
                </ul>
            </div>
        </div>
    </section>
    <section class="section-box mt-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12 col-sm-12 col-12">
                    <div id="applySuccessAlert" class="alert alert-success mb-4 d-none"></div>

                    <div class="single-body">
                        <div class="h5 font-bold">Job Description</div>
                        <div> {!! $job->description !!}</div>
                        @if ($job->responsibilities)
                            <div class="h5 font-bold">Responsibilities</div>
                            <div> {!! $job->responsibilities !!}</div>
                        @endif

                        @if ($job->skills)
                            <div class="h5 font-bold">Skills</div>
                            <div> {!! $job->skills !!}</div>
                        @endif

                        @if ($job->benefits)
                            <div class="h5 font-bold">Benefits</div>
                            <div> {!! $job->benefits !!}</div>
                        @endif
                        <div class="h5 font-bold">How to apply</div>
                        <div>
                            <p>Applicants interested in data entry specialist jobs in dubai for indian professionals,
                                and data entry jobs vacancies in dubai can apply by submitting:</p>
                            <ul>
                                <li>Updated resume</li>
                                <li>Short cover letter explaining your interest in data entry roles</li>
                                <li>Only shortlisted applicants will be contacted for further recruitment procedures.
                                </li>
                            </ul>
                            {{-- <p>
                                You may also explore other openings like-
                                <a href="https://dubaijobfinder.net/job/philosophy-lecturer">
                                    <span style="text-decoration: underline;">Philosophy Lecturer</span>
                                </a> ,
                                <a href="https://dubaijobfinder.net/job/production-engineer">
                                    <span style="text-decoration: underline;">Production Engineer</span>
                                </a> ,
                                <a href="https://dubaijobfinder.net/job/labor-relations-officer">
                                    <span style="text-decoration: underline;">Labor Relations Officer</span>
                                </a>
                            </p> --}}
                        </div>
                    </div>

                    <div class="single-apply-jobs">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                @if (auth('candidate')->check())

                                    @if ($alreadyApplied)
                                        <button class="btn btn-success mr-15" disabled>
                                            <i class="fi fi-rr-check me-1"></i>
                                            Already Applied
                                        </button>
                                    @else
                                        <a href="{{ route('jobs.apply.form', $job->slug) }}"
                                            class="btn btn-default mr-15">
                                            Apply Now
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('candidate.login') }}" class="btn btn-default mr-15">
                                        Login to Apply
                                    </a>

                                @endif
                            </div>
                            <div class="col-md-7 text-lg-end social-share">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->full()) }}"
                                    class="btn btn-border btn-sm mr-10"><img loading="lazy" alt="{{ $job->title }}"
                                        src="{{ asset('assets/imgs/theme/icons/share-fb.svg') }}" /> Share</a>
                                <a href="https://twitter.com/intent/tweet?text=Check out this job {{ trim($job->title) }}&url={{ urlencode(url()->full()) }}"
                                    target="_blank" rel="noopener noreferrer" class="btn btn-border btn-sm mr-10"><img
                                        loading="lazy" alt="{{ $job->title }}"
                                        src="{{ asset('assets/imgs/theme/icons/share-tw.svg') }}" /> Tweet</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-sm-12 col-12 pl-40 pl-lg-15 mt-lg-30">
                    <div class="sidebar-shadow">
                        <div class="sidebar-heading">
                            <div class="avatar-sidebar">
                                <figure><img loading="lazy" alt="{{ $job->title }}"
                                        src="{{ $job->employer->logo ? Storage::url($job->employer->logo) : Storage::url($generalSettings->site_favicon) }}" />
                                </figure>
                                <div class="sidebar-info">
                                    <span class="sidebar-company">{{ $job->employer->name }}</span>
                                    <a href="{{ $job->employer->website }}" target="_blank" rel="noopener noreferrer" class="sidebar-website-text" title="{{ $job->employer->website }}" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">{{ $job->employer->website }}</a>
                                </div>
                            </div>
                        </div>

                        <div class="text-description mt-15">
                            {{ Str::limit(html_entity_decode(strip_tags($job->employer->description)), 150) }}
                        </div>

                        <div class="text-start mt-20">
                            @if (auth('candidate')->check())

                                @if ($alreadyApplied)
                                    <button class="btn btn-success mr-15" disabled>
                                        <i class="fi fi-rr-check me-1"></i>
                                        Already Applied
                                    </button>
                                @else
                                    <a href="{{ route('jobs.apply.form', $job->slug) }}" class="btn btn-default mr-15">
                                        Apply Now
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('candidate.login') }}" class="btn btn-default mr-15">
                                    Login to Apply
                                </a>

                            @endif
                        </div>

                        <div class="sidebar-list-job">
                            <ul>
                                <li>
                                    <div class="sidebar-icon-item"><i class="fi-rr-briefcase"></i></div>
                                    <div class="sidebar-text-info">
                                        <span class="text-description">Job Type</span>
                                        <strong class="small-heading">{{ $job->job_type->getLabel() }}</strong>
                                    </div>
                                </li>
                                <li>
                                    <div class="sidebar-icon-item"><i class="fi-rr-marker"></i></div>
                                    <div class="sidebar-text-info">
                                        <span class="text-description">Location</span>
                                        <strong class="small-heading">{{ Str::title($job->location?->name) }}</strong>
                                    </div>
                                </li>
                                <li>
                                    <div class="sidebar-icon-item">
                                        <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 20.01"
                                            width="23" height="20.01">
                                            <style>
                                                .s0 {
                                                    fill: #88929b
                                                }
                                            </style>
                                            <path id="Layer copy" class="s0"
                                                d="M2.031 0.023c0.009 0.014 0.06 0.076 0.108 0.136 0.352 0.419 0.616 1.099 0.759 1.957 0.094 0.564 0.099 0.741 0.099 2.889v2.001h-0.963c-0.879 0 -0.98 -0.005 -1.152 -0.039a1.495 1.495 0 0 1 -0.741 -0.409c-0.149 -0.159 -0.145 -0.168 -0.136 0.313 0.011 0.398 0.016 0.442 0.074 0.658 0.092 0.343 0.218 0.598 0.409 0.826 0.26 0.313 0.524 0.488 0.902 0.605 0.081 0.023 0.251 0.032 0.853 0.037l0.752 0.011v1.994l-1.06 -0.007 -1.065 -0.007 -0.184 -0.074c-0.218 -0.087 -0.317 -0.152 -0.531 -0.343L0 10.431l0.009 0.439c0.011 0.407 0.014 0.453 0.071 0.66 0.2 0.731 0.683 1.254 1.32 1.424 0.159 0.044 0.221 0.046 0.885 0.055l0.711 0.009v2.061c0 1.244 -0.007 2.162 -0.018 2.318a8.05 8.05 0 0 1 -0.081 0.596c-0.149 0.858 -0.419 1.504 -0.805 1.923l-0.078 0.085h3.889c2.325 0 4.064 -0.009 4.319 -0.021 0.448 -0.023 1.449 -0.122 1.674 -0.17a10.35 10.35 0 0 0 1.23 -0.253 10.35 10.35 0 0 0 1.755 -0.6c0.175 -0.078 0.676 -0.333 0.81 -0.414 0.071 -0.041 0.156 -0.092 0.189 -0.108a4.6 4.6 0 0 0 0.458 -0.301l0.239 -0.17a8.05 8.05 0 0 0 0.582 -0.483 7.82 7.82 0 0 0 1.435 -1.755c0.053 -0.092 0.122 -0.207 0.152 -0.255 0.076 -0.129 0.389 -0.773 0.419 -0.869a0.46 0.46 0 0 1 0.041 -0.099c0.06 -0.078 0.405 -1.164 0.446 -1.401 0.014 -0.076 0.021 -0.087 0.078 -0.099 0.037 -0.007 0.573 -0.007 1.191 -0.002 1.237 0.009 1.237 0.009 1.511 0.136 0.154 0.071 0.2 0.103 0.37 0.258 0.223 0.2 0.202 0.232 0.189 -0.269q-0.011 -0.44 -0.041 -0.55c-0.078 -0.283 -0.097 -0.343 -0.166 -0.485a1.863 1.863 0 0 0 -1.086 -1.012l-0.189 -0.069 -0.768 -0.009 -0.766 -0.011 0.009 -0.269c0.009 -0.354 0.009 -1.056 -0.002 -1.417l-0.009 -0.29 1.026 -0.005c0.879 -0.005 1.042 0 1.138 0.025 0.29 0.081 0.485 0.191 0.724 0.409l0.133 0.124v-0.34c0 -0.405 -0.021 -0.584 -0.103 -0.851 -0.163 -0.54 -0.485 -0.943 -0.945 -1.191 -0.299 -0.161 -0.317 -0.166 -1.345 -0.172 -0.603 -0.005 -0.918 -0.014 -0.934 -0.028 -0.014 -0.014 -0.025 -0.037 -0.025 -0.055s-0.034 -0.163 -0.081 -0.32q-0.808 -2.853 -3.013 -4.533a10.35 10.35 0 0 0 -0.888 -0.589c-0.076 -0.044 -0.159 -0.09 -0.179 -0.103a19.55 19.55 0 0 0 -0.789 -0.382 5.75 5.75 0 0 0 -0.239 -0.101C13.644 0.547 12.282 0.214 11.242 0.11 11.072 0.094 10.847 0.069 10.743 0.06 10.274 0.007 9.623 0 5.927 0 2.804 0 2.019 0.007 2.031 0.023M9.637 1.019c0.777 0.046 1.256 0.106 1.815 0.241 1.707 0.405 2.907 1.26 3.779 2.691 0.081 0.133 0.421 0.828 0.471 0.968 0.241 0.651 0.359 1.037 0.462 1.548l0.076 0.368a0.46 0.46 0 0 1 0.016 0.154c-0.011 0.009 -2.321 0.014 -5.136 0.011l-5.117 -0.005 -0.007 -2.955c-0.002 -1.624 0 -2.974 0.007 -2.999l0.009 -0.044H7.647c0.897 0 1.794 0.009 1.989 0.021m6.842 8.057a35.65 35.65 0 0 1 0 1.861l-0.014 0.062 -5.232 -0.005 -5.23 -0.007 -0.005 -0.975c-0.005 -0.536 0 -0.982 0.005 -0.991 0.007 -0.011 2.236 -0.018 5.237 -0.018h5.226zm-0.235 3.949c0.011 0.034 -0.044 0.317 -0.156 0.777a8.625 8.625 0 0 1 -0.481 1.426 9.2 9.2 0 0 1 -0.359 0.706l-0.163 0.258a5.75 5.75 0 0 1 -1.679 1.677c-0.246 0.156 -0.752 0.423 -0.888 0.465a0.23 0.23 0 0 0 -0.069 0.03 5.75 5.75 0 0 1 -0.469 0.179c-0.448 0.159 -1.302 0.331 -1.987 0.402 -0.444 0.044 -0.515 0.046 -2.224 0.046H6.001v-5.967l5.081 -0.009a838.35 838.35 0 0 0 5.115 -0.016 0.046 0.046 0 0 1 0.048 0.028" />
                                        </svg>
                                    </div>
                                    <div class="sidebar-text-info">
                                        <span class="text-description">Salary</span>
                                        <strong class="small-heading">{{ $job->salary_range }} AED/Year</strong>
                                    </div>
                                </li>
                                <li>
                                    <div class="sidebar-icon-item"><i class="fi-rr-clock"></i></div>
                                    <div class="sidebar-text-info">
                                        <span class="text-description">Date posted</span>
                                        <strong class="small-heading">
                                            {{ $job->created_at->toFormattedDateString() }}</strong>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        @if ($job->employer->address)
                            <div class="sidebar-team-member pt-40">
                                <h6 class="small-heading">Contact Info</h6>
                                <div class="info-address">
                                    <span><i class="fi-rr-marker"></i> <span>{{ $job->employer->address }},
                                            {{ $job->employer->city }}, {{ $job->employer->state }},
                                            {{ $job->employer->country }}</span></span>
                                    <span><i class="fi-rr-headset"></i> <span>{{ $job->employer->phone }}
                                        </span></span>
                                    <span><i class="fi-rr-paper-plane"></i>
                                        <span>{{ $job->employer->email }}</span></span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-12">
                    <h4 class="heading-border"><span>Frequently asked questions</span></h4>
                    <livewire:pages.components.faqs-list section="jobs" :location="$job->location?->name" :category="$job->title" />
                    <div class="mb-4"></div>
                    <livewire:pages.components.recent-jobs />
                </div>
            </div>
        </div>
    </section>
</div>
