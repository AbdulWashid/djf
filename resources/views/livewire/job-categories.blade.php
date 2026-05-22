<section class="section-box">
    <div class="container">
        <div class="row align-items-end">
            <div class="col-lg-7">
                <h2 class="section-title mb-20 wow animate__animated animate__fadeInUp">Browse by category</h2>
                <p class="text-md-lh28 color-black-5 wow animate__animated animate__fadeInUp">Find the type of
                    work
                    you need, clearly defined and ready to start. Work begins as soon as you purchase and
                    provide
                    requirements.</p>
            </div>
            @unless ($showAll)
                <div class="col-lg-5 text-lg-end text-start wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                    <a href="{{ route('job-categories') }}" class="mt-sm-15 mt-lg-30 btn btn-border icon-chevron-right">Browse
                        all</a>
                </div>
            @endunless
        </div>
        <div class="row mt-70">
            @foreach ($categories as $category)
                <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                    <div class="card-grid hover-up wow animate__animated animate__fadeInUp">
                        <div class="text-center">
                            <a href="{{ route('jobs.category', ['category' => $category->slug]) }}">
                                <figure class="card-grid-image">
                                    <img alt="{{ $category->name }}"
                                        src="{{ $category->logo ? \Illuminate\Support\Facades\Storage::url($category->logo) : 'http://placehold.co/170x100?text=' . $category->slug }}" />
                                </figure>
                            </a>
                        </div>
                        <h5 class="text-center mt-20 card-heading">
                            <a
                                href="{{ route('jobs.category', ['category' => $category->slug]) }}">{{ $category->name }}</a>
                        </h5>
                        <p class="text-center text-stroke-40 mt-20">156 Available Vacancy</p>
                    </div>
                </div>
            @endforeach

            @unless ($showAll)
                <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                    <div class="card-grid hover-up wow animate__animated animate__fadeInUp h-100">
                        <div class="text-center mt-15">
                            <h3>All</h3>
                        </div>
                        <p class="text-center mt-30 text-stroke-40">Jobs are waiting for you</p>
                        <div class="text-center mt-30">
                            <div class="box-button-shadow">
                                <a href="{{ route('job-categories') }}" class="btn btn-default">Explore more</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endunless
        </div>

        @if ($showAll)
            <div class="mt-50">
                {{ $categories->links('livewire.custom-pagination') }}
            </div>
        @endif
    </div>
    {{-- <section class="section-box">
        <div class="container">
            <div class="row align-items-end">
                <div class="col-lg-7">
                    <h2 class="section-title mb-20 wow animate__animated animate__fadeInUp">Browse by category</h2>
                    <p class="text-md-lh28 color-black-5 wow animate__animated animate__fadeInUp">Find the type of
                        work
                        you need, clearly defined and ready to start. Work begins as soon as you purchase and
                        provide
                        requirements.</p>
                </div>
                <div class="col-lg-5 text-lg-end text-start wow animate__animated animate__fadeInUp"
                    data-wow-delay=".2s">
                    <a href="{{ route('jobs') }}" class="mt-sm-15 mt-lg-30 btn btn-border icon-chevron-right">Browse
                        all</a>
                </div>
            </div>
            <div class="row mt-70">
                <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                    <div class="card-grid hover-up wow animate__animated animate__fadeInUp">
                        <div class="text-center">
                            <a href="{{ route('jobs') }}" style="position:relative; left: 30%;">
                                <figure><img alt="Dubai Job Finder"
                                        src="{{ asset('assets/imgs/theme/icons/marketing.svg') }}" /></figure>
                            </a>
                        </div>
                        <h5 class="text-center mt-20 card-heading"><a href="{{ route('jobs') }}">Marketing &
                                Communication</a>
                        </h5>
                        <p class="text-center text-stroke-40 mt-20">156 Available Vacancy</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                    <div class="card-grid hover-up wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                        <div class="text-center mt-15">
                            <h3>18,265+</h3>
                        </div>
                        <p class="text-center mt-30 text-stroke-40">Jobs are waiting for you</p>
                        <div class="text-center mt-30">
                            <div class="box-button-shadow"><a href="{{ route('jobs') }}"
                                    class="btn btn-default">Explore
                                    more</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}
</section>
