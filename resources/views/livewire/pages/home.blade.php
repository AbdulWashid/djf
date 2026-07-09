<?php

use Livewire\Volt\Component;
use App\Models\HomePageMeta;
use Livewire\Attributes\Layout;

new #[Layout('components.frontend.main')] class extends Component {
    public $homeMeta;

    public function mount()
    {
        $this->homeMeta = HomePageMeta::query()->latest()->first();
        view()->share('pageTitle', $this->homeMeta?->meta_title ?: 'Jobs in Dubai | Job Search - Job Vacancies - Dubaijobfinder');

        view()->share('pageDescription', $this->homeMeta?->meta_description ?: 'Search Jobs in Middle East, Dubai. Post your Resume and find your dream job on Dubaijobfinder. We provide driver jobs, accountant jobs and more. Call us!');

        view()->share('metaKeywords', is_array($this->homeMeta?->meta_keywords) ? implode(', ', $this->homeMeta->meta_keywords) : ($this->homeMeta?->meta_keywords ?: null));

        view()->share(
            'twitterTags',
            $this->homeMeta?->twitter_tags ?:
            '
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:site" content="@Dubaijobfinder" />
            <meta name="twitter:title" content="Jobs in Dubai | Job Search - Job Vacancies - Dubaijobfinder" />
            <meta name="twitter:description" content="Search Jobs in Middle East, Dubai. Post your Resume and find your dream job on Dubaijobfinder. We provide driver jobs, accountant jobs and more. Call us!" />
            <meta name="twitter:url" content="' .
                url()->current() .
                '" />
            <meta name="twitter:image" content="' .
                asset('storage/sites/01K6J2R1QT4H6FZR7FDMM0MVY7.png') .
                '" />
            ',
        );

        view()->share(
            'ogTags',
            $this->homeMeta?->og_tags ?:
            '
            <meta property="og:title" content="Jobs in Dubai | Job Search - Job Vacancies - Dubaijobfinder" />
            <meta property="og:description" content="Search Jobs in Middle East, Dubai. Post your Resume and find your dream job on Dubaijobfinder. We provide driver jobs, accountant jobs and more. Call us!" />
            <meta property="og:url" content="' .
                url()->current() .
                '" />
            <meta property="og:type" content="website" />
            <meta property="og:site_name" content="Dubaijobfinder" />
            <meta property="og:image" content="' .
                asset('storage/sites/01K6J2R1QT4H6FZR7FDMM0MVY7.png') .
                '" />
            ',
        );
    }
}; ?>

<div>
    <section class="section-box">
        <div class="banner-hero hero-1">
            <div class="banner-inner">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="block-banner">
                            <span
                                class="text-small-primary text-small-primary--disk text-uppercase wow animate__animated animate__fadeInUp">
                                Best jobs place
                            </span>
                            {{-- <h1 class="heading-banner wow animate__animated animate__fadeInUp"> --}}
                            <h1 class="heading-banner">
                                The Easiest Way to Get Your New Job
                            </h1>
                            <div class="banner-description mt-30 wow animate__animated animate__fadeInUp"
                                data-wow-delay=".1s">Each month, more than 3 million job seekers turn to website in
                                their search for work, making over 140,000 applications every single day
                            </div>

                            <livewire:pages.components.homepage-search />

                            {{-- <div class="list-tags-banner mt-60 wow animate__animated animate__fadeInUp"
                                    data-wow-delay=".3s">
                                    <a class="btn btn-default btn-find text-white" href="{{ route('jobs') }}"
                                        role="button">Find now</a>
                                </div> --}}
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-imgs">
                            <img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/banner/banner.png') }}" class="img-responsive shape-1" />
                            <span class="union-icon"><img loading="lazy" alt="Dubai Job Finder"
                                    src="{{ asset('assets/imgs/banner/union.svg') }}"
                                    class="img-responsive shape-3" /></span>
                            <span class="congratulation-icon"><img loading="lazy" alt="Dubai Job Finder"
                                    src="{{ asset('assets/imgs/banner/congratulation.svg') }}"
                                    class="img-responsive shape-2" /></span>
                            <span class="docs-icon"><img loading="lazy" alt="Dubai Job Finder"
                                    src="{{ asset('assets/imgs/banner/docs.svg') }}"
                                    class="img-responsive shape-2" /></span>
                            <span class="course-icon"><img loading="lazy" alt="Dubai Job Finder"
                                    src="{{ asset('assets/imgs/banner/course.svg') }}"
                                    class="img-responsive shape-3" /></span>
                            <span class="web-dev-icon"><img loading="lazy" alt="Dubai Job Finder"
                                    src="{{ asset('assets/imgs/banner/web-dev.svg') }}"
                                    class="img-responsive shape-3" /></span>
                            <span class="tick-icon"><img loading="lazy" alt="Dubai Job Finder"
                                    src="{{ asset('assets/imgs/banner/tick.svg') }}"
                                    class="img-responsive shape-3" /></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <livewire:pages.job-categories />


    <livewire:pages.components.featured-jobs />

    {{-- <section class="section-box mt-50 mb-70 bg-patern">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-sm-12">
                        <div class="content-job-inner">
                            <h2 class="section-title heading-lg wow animate__animated animate__fadeInUp">The #1 Job
                                Board for Graphic Design Jobs</h2>
                            <div class="mt-40 pr-50 text-md-lh28 wow animate__animated animate__fadeInUp">Search and
                                connect with the right candidates faster. This talent seach gives you the opportunity to
                                find candidates who may be a perfect fit for your role
                            </div>
                            <div class="mt-40">
                                <div class="box-button-shadow wow animate__animated animate__fadeInUp"><a
                                        href="#" class="btn btn-default">Post
                                        a job now</a></div>
                                <a href="#" class="btn btn-link wow animate__animated animate__fadeInUp">Learn
                                    more</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                        <div class="box-image-job">
                            <figure class=" wow animate__animated animate__fadeIn"><img loading="lazy" alt="Dubai Job Finder"
                                    src="assets/imgs/blog/img-job.png" />
                            </figure>
                            <div class="job-top-creator">
                                <div class="job-top-creator-head">
                                    <h5>Top Freelancers</h5>
                                </div>
                                <ul>
                                    <li>
                                        <div>
                                            <figure><img loading="lazy" alt="Dubai Job Finder" src="assets/imgs/avatar/ava_13.png" />
                                            </figure>
                                            <div class="job-info-creator">
                                                <strong>Kate Adie</strong>
                                                <span>UI/UX Designer</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div>
                                            <figure><img loading="lazy" alt="Dubai Job Finder" src="assets/imgs/avatar/ava_14.png" />
                                            </figure>
                                            <div class="job-info-creator">
                                                <strong>John Lennon</strong>
                                                <span>Senior Art Director</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div>
                                            <figure><img loading="lazy" alt="Dubai Job Finder" src="assets/imgs/avatar/ava_15.png" />
                                            </figure>
                                            <div class="job-info-creator">
                                                <strong>Nadine Coyle</strong>
                                                <span>Photographer</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div>
                                            <figure><img loading="lazy" alt="Dubai Job Finder" src="assets/imgs/avatar/ava_16.png" />
                                            </figure>
                                            <div class="job-info-creator">
                                                <strong>Sarah Harding</strong>
                                                <span>Motion Designer</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}

    <div class="section-box">
        <div class="container">
            <h2 class="section-title text-center mb-15 wow animate__animated animate__fadeInUp">
                Jobs By Top Employers
            </h2>

            <ul class="list-partners">
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay="0s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/samsung.svg') }}" />
                        </figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".1s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/google.svg') }}" />
                        </figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".2s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/facebook.svg') }}" />
                        </figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".3s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/pinterest.svg') }}" /></figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".4s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/avaya.svg') }}" />
                        </figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".5s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/forbes.svg') }}" />
                        </figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".1s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/avis.svg') }}" />
                        </figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".2s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/nielsen.svg') }}" />
                        </figure>
                    </a>
                </li>
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".3s">
                    <a href="#">
                        <figure><img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/jobs/logos/doordash.svg') }}" />
                        </figure>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <livewire:pages.components.recent-post-home />

    <section class="section-box mt-50 mb-60">
        <div class="container">
            <h2 class="section-title heading-lg wow animate__animated animate__fadeInUp text-center">Why Dubai Job
                Finder?</h2>
            <div class="row">

                <div class="col-md-12">
                    <livewire:pages.components.faqs-list section="homepage" />
                </div>
            </div>
        </div>
    </section>

    <livewire:pages.components.subscribe />
</div>
