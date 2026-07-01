<?php

use Livewire\Volt\Component;
use App\Models\JobCategory;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('components.frontend.main')] class extends Component {
    use WithPagination;

    public ?bool $showAll = false;
    public int $categoryCount = 0;

    public function mount(): void
    {
        $this->showAll = request()->segment(1) == 'job-categories';

        $this->categoryCount = rememberIfEnabled('job_categories_active_count', now()->addMinutes(30), fn() => JobCategory::query()->active()->count());

        if ($this->showAll) {
            view()->share('pageTitle', 'Job Categories');
            view()->share('pageDescription', 'Browse all job categories.');
        }
    }

    public function with(): array
    {
        if ($this->showAll) {
            $page = $this->getPage();

            $categories = rememberIfEnabled('job_categories_all_page_' . $page, now()->addMinutes(30), function () {
                return JobCategory::query()->active()->select('id', 'name', 'slug', 'logo')->withCount('openings')->orderBy('position')->orderByDesc('openings_count')->orderBy('name')->paginate(12);
            });
        } else {
            $categories = rememberIfEnabled('job_categories_home', now()->addMinutes(30), function () {
                return JobCategory::query()->active()->select('id', 'name', 'slug', 'logo')->withCount('openings')->orderBy('position')->orderByDesc('openings_count')->orderBy('name')->limit(7)->get();
            });
        }

        return [
            'categories' => $categories,
        ];
    }
}; ?>

<div>
    <section class="section-box">
        <div class="container">
            <div class="row align-items-end">
                <div class="col-lg-7">
                    @if ($showAll)
                        <h1 class="h1 section-title mb-20 wow animate__animated animate__fadeInUp">
                            Browse by category
                        </h1>
                    @else
                        <h2 class="h2 section-title mb-20 wow animate__animated animate__fadeInUp">
                            Browse by category
                        </h2>
                    @endif
                    <p class="text-md-lh28 color-black-5 wow animate__animated animate__fadeInUp">Find the type of
                        work
                        you need, clearly defined and ready to start. Work begins as soon as you purchase and
                        provide
                        requirements.
                    </p>
                </div>
                @unless ($showAll)
                    <div class="col-lg-5 text-lg-end text-start wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                        <a href="{{ route('job-categories') }}" class="mt-sm-15 mt-lg-30 btn btn-border icon-chevron-right">
                            Browse all
                        </a>
                    </div>
                @endunless
            </div>
            <div class="row mt-70">
                @foreach ($categories as $category)
                    <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                        <div class="card-grid hover-up wow animate__animated animate__fadeInUp">
                            <div class="text-center">
                                <a href="{{ route('jobs.category', ['category' => $category->slug]) }}">
                                    <figure class="d-flex justify-content-center">
                                        <img loading="lazy" alt="{{ $category->name }}"
                                            src="{{ $category->logo ?: 'http://placehold.co/125x125?text=' . $category->slug }}"
                                            height="125" width="125" />
                                    </figure>
                                </a>
                            </div>
                            <h5 class="text-center mt-20 card-heading">
                                <a href="{{ route('jobs.category', ['category' => $category->slug]) }}">
                                    {{ $category->name }}
                                </a>
                            </h5>
                            <p class="text-center text-stroke-40 mt-20">
                                {{ number_format($category->openings_count) }}
                                {{ \Illuminate\Support\Str::plural('Available Vacancy', $category->openings_count) }}
                            </p>
                        </div>
                    </div>
                @endforeach

                @unless ($showAll)
                    <div class="col-lg-3 col-md-6 col-sm-12 col-12">
                        <div class="card-grid hover-up wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                            <div class="text-center mt-45">
                                <h2 class="mb-0 h2">All jobs category</h2>
                            </div>
                            <p class="text-center mt-20 text-stroke-40">{{ number_format($categoryCount) }} Categories</p>
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
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
