<section class="section-box mt-50">
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
                    <div class="swiper-wrapper pb-70 pt-5">
                        @foreach ($posts as $recentPost)
                            <div class="swiper-slide">
                                <div class="card-grid-3 hover-up">
                                    <div class="card-image mb-3 overflow-hidden">
                                        <a href="{{ $recentPost->getUrl() }}">
                                            @if ($recentPost->hasFeaturedImage())
                                                <img src="{{ $recentPost->getFeaturedImageUrl('medium') }}"
                                                    alt="{{ $recentPost->title }}" class="img-fluid w-100"
                                                    style="height: 200px; object-fit: cover;" />
                                            @else
                                                <img src="https://placehold.co/460x200?text={{ urlencode(substr($recentPost->title, 0, 20)) }}"
                                                    alt="{{ $recentPost->title }}" class="img-fluid w-100"
                                                    style="height: 200px; object-fit: cover;" />
                                            @endif
                                        </a>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between flex-column text-muted small mb-3">
                                            <span>{{ $recentPost->published_at?->format('M d, Y') ?? '' }}</span>
                                            <span>{{ $recentPost->author->firstname ?? 'Anonymous' }}</span>
                                        </div>
                                        <h3 class="h5 text-primary mb-2">
                                            <a href="{{ $recentPost->getUrl() }}"
                                                class="text-decoration-none">{{ $recentPost->title }}</a>
                                        </h3>
                                        <p class="mb-0 text-muted small">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($recentPost->content ?? ($recentPost->excerpt ?? '')), 100) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="swiper-slide">
                                <div class="card-grid-3 hover-up">
                                    <div class="text-center card-grid-3-image">
                                        <a href="{{ $recentPost->getUrl() }}">
                                            <figure>
                                                @if ($recentPost->hasFeaturedImage())
                                                    <img src="{{ $recentPost->getFeaturedImageUrl('medium') }}"
                                                        alt="{{ $recentPost->title }}" />
                                                @else
                                                    <img src="https://placehold.co/300x165?text={{ substr($recentPost->title, 0, 10) }}"
                                                        alt="{{ $recentPost->title }}" />
                                                @endif
                                            </figure>
                                        </a>
                                    </div>
                                    <div class="card-block-info">
                                        <div class="row">
                                            <div class="col-lg-6 col-6 text-start">
                                                <span>{{ $recentPost->author->username ?? 'Anonymous' }}</span>
                                            </div>
                                            <div class="col-lg-6 col-6 text-end">
                                                <span>{{ $recentPost->published_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                        <h5 class="mt-15 heading-md">
                                            <a href="{{ $recentPost->getUrl() }}">{{ $recentPost->title }}</a>
                                        </h5>
                                        <div class="card-2-bottom mt-50">
                                            <div class="row">
                                                <div class="col-lg-9 col-8">
                                                    <a href="{{ $recentPost->getUrl() }}"
                                                        class="btn btn-border btn-brand-hover">
                                                        Keep reading
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        @endforeach
                    </div>
                    <div class="swiper-pagination swiper-pagination3"></div>
                </div>
                <div class="swiper-button-next mr-4"></div>
                <div class="swiper-button-prev ml-4"></div>
            </div>
        </div>
    </div>
</section>
