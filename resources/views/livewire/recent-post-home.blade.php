<section class="section-box mt-50 recent-post-slider">
    <style>
        .recent-post-slider .swiper-slide {
            height: auto;
            display: flex;
        }

        .recent-post-slider .card-grid-3 {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .recent-post-slider .card-body {
            flex: 1;
        }

        .recent-post-slider .swiper-button-next {
            right: -50px;
        }

        .recent-post-slider .swiper-button-prev {
            left: -50px;
        }

        .card-grid-3 {
            height: 100%;
        }

        .card-image img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .card-image img {
                height: 180px;
            }
        }

        @media (max-width: 576px) {
            .card-image img {
                height: 150px;
            }

            .swiper-button-next,
            .swiper-button-prev {
                display: none;
            }
        }
    </style>
    <div class="container">
        <div class="row align-items-end">
            <div class="col-lg-7 col-md-7">
                <h2 class="section-title mb-20 wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".1s">
                    From blog
                </h2>
                <p class="text-md-lh28 color-black-5 wow animate__animated animate__fadeInUp hover-up"
                    data-wow-delay=".1s">
                    Latest News & Events
                </p>
            </div>
            <div class="col-lg-5 col-md-5 text-lg-end text-start">
                <a href="{{ route('blog') }}"
                    class="btn btn-border icon-chevron-right wow animate__animated animate__fadeInUp hover-up mt-15"
                    data-wow-delay=".1s">
                    View more
                </a>
            </div>
        </div>
        <div class="row mt-70">
            <div class="box-swiper">
                <div class="swiper-container swiper-group-3">
                    <div class="swiper-wrapper pb-4 pb-md-5 align-items-stretch">
                        @foreach ($posts as $recentPost)
                            <div class="swiper-slide h-auto">
                                <div class="card-grid-3 hover-up h-100 d-flex flex-column">
                                    <div class="card-image mb-3 overflow-hidden">
                                        <a href="{{ $recentPost->getUrl() }}">
                                            @if ($recentPost->hasLargeImage())
                                                <img src="{{ $recentPost->getLargeImageUrl() }}"
                                                    alt="{{ $recentPost->title }}"
                                                    class="img-fluid w-100 blog-card-image">
                                            @else
                                                <img src="https://placehold.co/1600x900?text={{ urlencode(substr($recentPost->title, 0, 20)) }}"
                                                    alt="{{ $recentPost->title }}"
                                                    class="img-fluid w-100 blog-card-image">
                                            @endif
                                        </a>
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                        <div class="d-flex justify-content-between flex-wrap text-muted small mb-3">
                                            <span>{{ $recentPost->published_at?->format('M d, Y') ?? '' }}</span>
                                            <span>{{ $recentPost->author->firstname ?? 'Anonymous' }}</span>
                                        </div>

                                        <h3 class="h5 text-primary mb-2">
                                            <a href="{{ $recentPost->getUrl() }}" class="text-decoration-none">
                                                {{ $recentPost->title }}
                                            </a>
                                        </h3>

                                        <p class="mb-0 text-muted small">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($recentPost->content ?? ($recentPost->excerpt ?? '')), 100) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="swiper-pagination swiper-pagination3"></div>
                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>
</section>
