<?php

use Livewire\Volt\Component;
use App\Models\Opening;

new class extends Component {
    public $jobs;

    public function mount()
    {
        $this->jobs = rememberIfEnabled('recent_jobs', now()->addMinutes(30), fn() => Opening::where('status', 1)->with('employer')->orderBy('created_at', 'desc')->take(5)->get());
    }
}; ?>

<div>
    {{-- @dd() --}}
    <section>
        @if ($jobs)
            <div class="single-recent-jobs">
                <h4 class="heading-border"><span>Recent jobs</span></h4>
                <div class="list-recent-jobs">
                    @foreach ($jobs as $job)
                        @continue(request()->segment(2) == $job->slug)
                        <div class="card-job hover-up wow animate__animated animate__fadeInUp">
                            <div class="card-job-top">
                                <div class="card-job-top--image">
                                    <figure>
                                        <img loading="lazy" alt="{{ $job->title ?? 'Dubai job Finder' }}"
                                            src="{{ $job->employer->logo ? Storage::url($job->employer->logo) : Storage::url($generalSettings->site_favicon) }}" />
                                    </figure>
                                </div>
                                <div class="card-job-top--info">
                                    <div class="h6 card-job-top--info-heading"><a
                                            href="{{ route('jobs.show', $job->slug) }}">{{ $job->title }}</a></div>
                                    <div class="row">
                                        <div class="col-lg-7">
                                            <span class="card-job-top--company">{{ $job->employer->name }}</span> &nbsp;
                                            <span class="card-job-top--location text-sm"><i class="fi-rr-marker"></i>
                                                {{ $job->location }}</span>
                                            <span class="card-job-top--type-job text-sm"><i class="fi-rr-briefcase"></i>
                                                {{ $job->job_type->getLabel() }}</span>
                                            <span class="card-job-top--post-time text-sm"><i class="fi-rr-clock"></i>
                                                {{ $job->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div class="col-lg-5 text-lg-end">
                                            <span class="card-job-top--price">{{ $job->salary_range }}<span>
                                                    AED/Year</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-job-description mt-20">
                                {!! Str::excerpt($job->description) !!}
                            </div>
                            {{-- <div class="card-job-bottom mt-25">
                                <div class="row">
                                    <div class="col-lg-9 col-sm-8 col-12">
                                        <div class="btn btn-small background-urgent btn-pink mr-5">Urgent</div>
                                        <a href="job-grid-2.html"
                                            class="btn btn-small background-blue-light mr-5">Senior</a>
                                        <a href="job-grid.html" class="btn btn-small background-6 disc-btn">Full
                                            time</a>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    @endforeach


                    <div class="mb-20">
                        <a href="{{ route('jobs') }}" class="btn btn-default">Explore more</a>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>
