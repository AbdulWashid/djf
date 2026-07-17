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
                        @push('css')
                            <style>
                                .modern-banner-card {
                                    border-radius: 24px !important;
                                    box-shadow: 0 25px 50px -12px rgba(68, 70, 144, 0.25) !important;
                                    border: 4px solid #ffffff !important;
                                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                                }

                                .modern-banner-card:hover {
                                    transform: translateY(-5px) scale(1.01) !important;
                                    box-shadow: 0 35px 60px -15px rgba(68, 70, 144, 0.35) !important;
                                }

                                /* Glassmorphic Cards */
                                .glass-card-modern {
                                    background: rgba(255, 255, 255, 0.85) !important;
                                    backdrop-filter: blur(12px) !important;
                                    -webkit-backdrop-filter: blur(12px) !important;
                                    border: 1.5px solid rgba(255, 255, 255, 0.7) !important;
                                    border-radius: 16px !important;
                                    box-shadow: 0 15px 35px rgba(31, 41, 55, 0.08) !important;
                                    display: flex !important;
                                    align-items: center !important;
                                    padding: 12px 16px !important;
                                    font-family: 'Montserrat', sans-serif !important;
                                    gap: 12px !important;
                                    transition: all 0.3s ease !important;
                                    width: max-content !important;
                                    min-width: 190px !important;
                                }

                                .glass-card-modern:hover {
                                    transform: translateY(-3px) !important;
                                    box-shadow: 0 20px 40px rgba(31, 41, 55, 0.12) !important;
                                    border-color: rgba(255, 255, 255, 0.9) !important;
                                }

                                .glass-card-icon {
                                    display: flex !important;
                                    align-items: center !important;
                                    justify-content: center !important;
                                    width: 40px !important;
                                    height: 40px !important;
                                    border-radius: 12px !important;
                                    flex-shrink: 0 !important;
                                }

                                .icon-wrap-orange {
                                    background: rgba(255, 114, 67, 0.15) !important;
                                    color: #FF7243 !important;
                                }

                                .icon-wrap-blue {
                                    background: rgba(81, 146, 255, 0.15) !important;
                                    color: #5192ff !important;
                                }

                                .icon-wrap-purple {
                                    background: rgba(68, 70, 144, 0.15) !important;
                                    color: #444690 !important;
                                }

                                .icon-wrap-green {
                                    background: rgba(16, 185, 129, 0.15) !important;
                                    color: #10b981 !important;
                                }

                                .glass-card-info {
                                    display: flex !important;
                                    flex-direction: column !important;
                                    text-align: left !important;
                                }

                                .glass-card-title {
                                    font-weight: 700 !important;
                                    font-size: 14px !important;
                                    color: #1f2938 !important;
                                    line-height: 1.2 !important;
                                }

                                .glass-card-subtitle {
                                    font-size: 11px !important;
                                    color: #6b7280 !important;
                                    margin-top: 2px !important;
                                    line-height: 1.2 !important;
                                }

                                .glass-card-apply-btn {
                                    background-color: #444690 !important;
                                    color: #ffffff !important;
                                    font-size: 10px !important;
                                    font-weight: 600 !important;
                                    padding: 5px 10px !important;
                                    border-radius: 6px !important;
                                    text-decoration: none !important;
                                    margin-left: auto !important;
                                    transition: background-color 0.2s !important;
                                }

                                .glass-card-apply-btn:hover {
                                    background-color: #343777 !important;
                                    color: #ffffff !important;
                                }



                                .banner-hero .banner-inner .banner-imgs .rating-card-floating {
                                    position: absolute;
                                    bottom: 15%;
                                    left: -130px;
                                    z-index: 10;
                                }
                            </style>
                        @endpush

                        <div class="banner-imgs">
                            <img loading="lazy" alt="Dubai Job Finder"
                                src="{{ asset('assets/imgs/banner/banner_modern.jpg') }}"
                                class="img-responsive shape-1 modern-banner-card" />
                            <span class="congratulation-icon shape-2">
                                <div class="glass-card-modern">
                                    <div class="glass-card-icon icon-wrap-orange">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="2" y="7" width="20" height="14" rx="2"
                                                ry="2"></rect>
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                        </svg>
                                    </div>
                                    <div class="glass-card-info">
                                        <span class="glass-card-title">Job Found!</span>
                                        <span class="glass-card-subtitle">Perfect Match Found!</span>
                                    </div>
                                    <div style="color: #10b981; margin-left: 4px; display: flex; align-items: center;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                </div>
                            </span>
                            <span class="course-icon shape-3">
                                <div class="glass-card-modern">
                                    <div class="glass-card-icon icon-wrap-blue">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                            <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path>
                                        </svg>
                                    </div>
                                    <div class="glass-card-info">
                                        <span class="glass-card-title">10000+</span>
                                        <span class="glass-card-subtitle">Best Opportunities</span>
                                    </div>
                                </div>
                            </span>
                            <span class="web-dev-icon shape-3">
                                <div class="glass-card-modern" style="min-width: 220px !important;">
                                    <div class="glass-card-icon icon-wrap-purple">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="16 18 22 12 16 6"></polyline>
                                            <polyline points="8 6 2 12 8 18"></polyline>
                                        </svg>
                                    </div>
                                    <div class="glass-card-info">
                                        <span class="glass-card-title">Software Engineer</span>
                                        <span class="glass-card-subtitle">Dubai, UAE • Full-time</span>
                                    </div>
                                    <a href="#feature-jobs" class="glass-card-apply-btn">Apply Now</a>
                                </div>
                            </span>
                            <span class="rating-card-floating shape-2">
                                <div class="glass-card-modern" style="min-width: 170px !important;">
                                    <div style="display: flex; margin-right: 4px;">
                                        <img src="{{ asset('assets/imgs/avatar/ava_1.png') }}"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 1.5px solid #fff; margin-right: -8px; z-index: 5;" />
                                        <img src="{{ asset('assets/imgs/avatar/ava_3.png') }}"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 1.5px solid #fff; margin-right: -8px; z-index: 4;" />
                                        <img src="{{ asset('assets/imgs/avatar/ava_5.png') }}"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 1.5px solid #fff; margin-right: -8px; z-index: 3;" />
                                        <img src="{{ asset('assets/imgs/avatar/ava_6.png') }}"
                                            style="width: 24px; height: 24px; border-radius: 50%; border: 1.5px solid #fff; margin-right: -8px; z-index: 2;" />
                                        <div
                                            style="width: 24px; height: 24px; border-radius: 50%; background: #444690; color: #fff; font-size: 8px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 1.5px solid #fff; z-index: 1;">
                                            99+</div>
                                    </div>
                                    <div class="glass-card-info" style="margin-left: 8px;">
                                        <span class="glass-card-title"
                                            style="display: flex; align-items: center; gap: 4px;">
                                            <span style="color: #FFD333;">★</span> 4.9
                                        </span>
                                        <span class="glass-card-subtitle">(3.7k reviews)</span>
                                    </div>
                                </div>
                            </span>
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
