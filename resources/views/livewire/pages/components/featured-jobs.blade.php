<?php

use Livewire\Volt\Component;
use App\Models\Opening;

new class extends Component {
    public $jobs;

    public function mount(): void
    {
        $this->jobs = rememberIfEnabled('feature_jobs', now()->addMinutes(30), function () {
            return Opening::select('id', 'employer_id', 'title', 'slug', 'location', 'job_type', 'created_at', 'salary_range', 'description')
                ->where('featured', 1)
                ->with([
                    'employer' => function ($query) {
                        $query->select('id', 'logo', 'name');
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();
        });
    }
}; ?>

<div>
    <section class="section-box mt-80">
        <div class="container">
            <h2 class="section-title text-center mb-15 wow animate__animated animate__fadeInUp">Featured Jobs</h2>
            <div class="text-normal text-center mb-60 color-black-5 box-mw-60 wow animate__animated animate__fadeInUp">
                The #1 Job Board for Hiring Creative Professionals
            </div>
            <div class="list-recent-jobs list-job-2-col">
                <div class="row">
                    @foreach ($jobs as $job)
                        <div class="col-lg-6 col-md-12 col-sm-12 col-12">
                            <!-- Item job -->
                            <div class="card-job hover-up wow animate__animated animate__fadeInUp"
                                style="border-color: #FFC107;">
                                <div class="card-job-top">
                                    <div class="card-job-top--image">
                                        <figure><img loading="lazy" alt="{{ $job->title }}"
                                                src="{{ $job->employer->logo ? Storage::url($job->employer->logo) : Storage::url($generalSettings->site_favicon) }}" />
                                        </figure>
                                    </div>
                                    <div class="card-job-top--info">
                                        <h6 class="card-job-top--info-heading">
                                            <a href="{{ route('jobs.show', $job->slug) }}">{{ $job->title }}</a>
                                        </h6>
                                        <div class="row">
                                            <div class="">
                                                <span class="card-job-top--company">{{ $job->employer->name }}</span>
                                                &nbsp;
                                                <span class="card-job-top--location text-sm">
                                                    <i class="fi-rr-marker"></i>
                                                    {{ $job->location }}
                                                </span>
                                                <span class="card-job-top--type-job text-sm">
                                                    <i class="fi-rr-briefcase"></i>
                                                    {{ $job->job_type->getLabel() }}
                                                </span>
                                                <span class="card-job-top--post-time text-sm">
                                                    <i class="fi-rr-clock"></i>
                                                    {{ $job->created_at->format('M d, Y') }}
                                                </span>
                                            </div>
                                            <div class="text-nowrap mt-3">
                                                <span class="card-job-top--price">{{ $job->salary_range }}<span>
                                                        AED/Year
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-job-description mt-20">
                                    {!! Str::excerpt(strip_tags($job->description)) !!}
                                </div>
                            </div>
                            <!-- End item job -->
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
