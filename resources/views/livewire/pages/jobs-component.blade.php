<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use App\Enums\EmploymentType;
use App\Models\JobCategory;
use App\Models\Opening;
use App\Models\Location;

new #[Layout('components.frontend.main')] class extends Component {
    use WithPagination;

    public $categories;
    public $locations;
    public $job_types;
    public $q;
    public $location;
    public $category;
    public $job_type = [];
    public $salary_range;
    public $salary_ranges;

    public function mount($location = null, $category_slug = null): void
    {
        $this->categories = rememberIfEnabled('job_categories', now()->addHours(12), fn() => JobCategory::active()->pluck('name', 'id'));
        $this->locations = rememberIfEnabled('job_locations', now()->addHours(12), fn() => Location::orderBy('name')->get(['id', 'name']));
        $this->job_types = EmploymentType::toOptionsArray();
        $this->q = $this->normalizeQuery(request()->query('q'));

        if ($location) {
            // Single-segment URL could be a category slug OR a location slug
            $cat = JobCategory::where('slug', Str::slug($location))->first();

            if ($cat) {
                $this->category = $cat->id;
                $this->location = null;
            } else {
                $foundLocation = $this->locations->first(fn($loc) => Str::slug($loc->name) === Str::slug($location));
                $this->location = $foundLocation?->id;
            }
        }

        if ($category_slug) {
            // location is already resolved above from $location — no need to re-derive it here
            $cat = JobCategory::where('slug', Str::slug($category_slug))->first();
            if ($cat) {
                $this->category = $cat->id;
            }
        }

        view()->share('pageType', 'job_listing');
        view()->share('pageTitle', $this->getSeoTitle());
        view()->share('pageDescription', $this->getSeoDescription());
        view()->share('schemaData', $this->buildSchemas());
    }

    public function hydrate(): void
    {
        $this->q = $this->normalizeQuery(request()->query('q', $this->q));
    }

    public function updatedLocation($value)
    {
        $this->updateUrl();
    }

    public function updatedCategory($value)
    {
        $this->updateUrl();
    }

    private function updateUrl()
    {
        $locationSlug = Location::find($this->location)?->name;

        $locationSlug = $locationSlug ? Str::slug($locationSlug) : null;

        $categorySlug = null;

        if ($this->category) {
            $categorySlug = JobCategory::where('id', $this->category)->value('slug');
        }

        $url = route('jobs');
        if ($locationSlug && $categorySlug) {
            $url = route('jobs.location.category', ['location' => $locationSlug, 'category_slug' => $categorySlug]);
        } elseif ($locationSlug) {
            $url = route('jobs.location', ['location' => $locationSlug]);
        } elseif ($categorySlug) {
            $url = route('jobs.category', ['category' => $categorySlug]);
        }

        $url = rtrim($url, '/') . '/';
        if (!empty($this->q)) {
            $url .= '?q=' . urlencode($this->normalizeQuery($this->q));
        }

        $this->dispatch('url-updated', ['url' => $url]);
        // $this->dispatch('seo-updated', [
        //     'title' => str_replace(['{location}', '{Category}'], [Str::title($this->location ?? 'Dubai'), $this->category ? $this->selectedCategoryName() ?? '' : ''], $this->pageTitle),
        //     'description' => str_replace(['{location}', '{Category}'], [Str::title($this->location ?? 'Dubai'), $this->category ? $this->selectedCategoryName() ?? '' : ''], $this->pageDescription),
        // ]);
        $this->dispatch('seo-updated', [
            'title' => $this->getSeoTitle(),
            'description' => $this->getSeoDescription(),
        ]);
        $this->dispatch('schema-updated', [
            'schemas' => $this->buildSchemas(),
        ]);
    }

    protected function buildSchemas(): array
    {
        $schemas = [];

        $jobs = Opening::active()
            ->with(['employer', 'location'])
            ->limit(10)
            ->get();

        foreach ($jobs as $job) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'JobPosting',
                'title' => $job->title,
                'description' => strip_tags($job->description),
                'datePosted' => optional($job->created_at)->toDateString(),
                'employmentType' => $job->job_type,
                'url' => route('jobs', $job->slug),

                'hiringOrganization' => [
                    '@type' => 'Organization',
                    'name' => $job->employer?->name,
                    'logo' => $job->employer?->logo ? asset('storage/' . $job->employer->logo) : null,
                ],

                'jobLocation' => [
                    '@type' => 'Place',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => $job->location?->name,
                        'addressCountry' => 'AE',
                    ],
                ],
            ];
        }

        return $schemas;
    }
    public function clear()
    {
        // reset component search filters
        $this->location = null;
        $this->category = null;
        $this->job_type = [];
        $this->salary_range = null;
        $this->q = null;
        $this->updateUrl();
        $this->resetPage();
        $this->dispatch('reset-select2');
    }

    public function search()
    {
        // $query = Opening::query()->active()->with('employer');
        $query = Opening::select(['id', 'slug', 'title', 'location_id', 'description', 'salary_range', 'job_type', 'job_category_id', 'employer_id'])
            ->active()
            ->with(['employer:id,name,logo', 'location:id,name']);

        $keyword = $this->normalizeQuery($this->q ?: request()->query('q', ''));

        // Keyword filter: search by job title or employer name
        // if ($keyword !== '') {
        //     $query->where(function ($q) use ($keyword) {
        //         $q->where('title', 'like', "%{$keyword}%")->orWhereHas('employer', function ($q2) use ($keyword) {
        //             $q2->where('name', 'like', "%{$keyword}%");
        //         });
        //     });
        // }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('job_type', 'like', "%{$keyword}%")
                    ->orWhereHas('location', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($this->location) {
            $query->where('location_id', $this->location);
        }

        if ($this->category) {
            $query->where('job_category_id', $this->category);
        }

        if ($this->job_type) {
            $query->whereIn('job_type', (array) $this->job_type);
        }

        return $query->paginate(12);
    }

    private function normalizeQuery(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : Str::lower($value);
    }

    public function jobs()
    {
        $page = request('page', 1);

        // $key = 'jobs:' . md5(json_encode([$this->location, $this->category, $this->job_type, $this->q, $page]));

        $version = Cache::get('jobs_cache_version', 1);

        $key = "jobs:v{$version}:" . md5(json_encode([$this->location, $this->category, $this->job_type, $this->q, $page]));

        return rememberIfEnabled($key, now()->addMinutes(30), function () {
            return $this->search();
        });
        // return $this->search();
    }
    protected function selectedCategoryName(): ?string
    {
        if (!$this->category) {
            return null;
        }
        return $this->categories[$this->category] ?? null;

        // return JobCategory::find($this->category)?->name;
    }
    protected function getHeading(): string
    {
        $category = $this->selectedCategoryName();
        $location = Location::find($this->location)?->name;

        if ($category && $location) {
            return "{$category} in {$location}";
        }

        if ($location) {
            return "Jobs in {$location}";
        }

        if ($category) {
            return "{$category}";
        }

        return 'Latest Jobs';
    }

    protected function getBreadcrumbTitle(): string
    {
        return $this->getHeading();
    }

    protected function getSeoTitle(): string
    {
        return $this->getHeading() . ' | Dubai Job Finder';
    }

    protected function getSeoDescription(): string
    {
        $category = $this->selectedCategoryName();
        $location = $this->location ? Str::title(Location::find($this->location)?->name) : null;
        if ($category && $location) {
            return "Looking for {$category} vacancies in {$location} ? Explore updated job openings and top employers on Dubai Job Finder. Apply now!";
        }

        if ($location) {
            return "Discover the latest jobs in {$location}. Find full-time, part-time, and urgent vacancies across multiple industries on  Dubai Job Finder.";
        }

        if ($category) {
            return "Explore the latest {$category}. Apply online for verified vacancies and career opportunities on  Dubai Job Finder.";
        }

        return 'Browse the latest job vacancies across the UAE. Search by category and location and apply online with  Dubai Job Finder.';
    }
}; ?>

<div>
    <section class="section-box">
        <div class="box-head-single">
            {{-- <div class="container">
                    <h1 class="h1">Currently available Jobs @if (!empty($location))
                            in {{ Str::ucwords($location) }}
                        @endif!</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li>Jobs listing</li>
                    </ul>
                </div> --}}
            <div class="container">
                <h1 class="h1">{{ $this->getHeading() }}</h1>

                <ul class="breadcrumbs">
                    <li>
                        <a href="{{ route('home') }}">Home</a>
                    </li>

                    <li>
                        <a href="{{ route('jobs') }}">Jobs</a>
                    </li>

                    @if ($location || $category)
                        <li>{{ $this->getBreadcrumbTitle() }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </section>
    <section class="section-box mt-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-12 col-sm-12 col-12">
                    <div class="sidebar-shadow none-shadow mb-30">
                        <div class="sidebar-filters">
                            <div class="filter-block mb-30">
                                <h5 class="medium-heading mb-15">Location </h5>
                                <div class="form-group select-style select-style-icon" wire:ignore>
                                    <select id="location-select" wire:model.live="location"
                                        class="location-select form-control form-icons">
                                        <option value="">Select Location</option>
                                        @foreach ($locations as $loc)
                                            <option value="{{ $loc->id }}">
                                                {{ $loc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fi-rr-briefcase"></i>
                                </div>
                            </div>
                            <div class="filter-block mb-30">
                                <h5 class="medium-heading mb-15">Category</h5>
                                <div class="form-group select-style select-style-icon" wire:ignore>
                                    <select id="category-select" wire:model.live="category"
                                        class="form-control form-icons">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $cat_id => $categ)
                                            <option value="{{ $cat_id }}">{{ $categ }}</option>
                                        @endforeach
                                    </select>
                                    <i class="fi-rr-briefcase"></i>
                                </div>
                            </div>
                            <div class="filter-block mb-30">
                                <h5 class="medium-heading mb-15">Job type</h5>
                                <div class="form-group">
                                    <ul class="list-checkbox">
                                        @foreach ($job_types as $enum_key => $jobType)
                                            <li>
                                                <label class="cb-container">
                                                    <input type="checkbox" wire:model.live="job_type"
                                                        value="{{ $enum_key }}"> <span
                                                        class="text-small">{{ $jobType }}</span>
                                                    <span class="checkmark"></span>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            {{-- <div class="filter-block mb-40"> 
                                <h5 class="medium-heading mb-25">Salary Range</h5> 
                                <div class=""> 
                                    <div class="row mb-20"> 
                                        <div class="col-sm-12"> 
                                            <div id="slider-range"></div> 
                                        </div> 
                                    </div> 
                                    <div class="row"> 
                                        <div class="col-lg-6"> 
                                            <label class="lb-slider">From</label> 
                                            <div class="form-group minus-input"> 
                                                <input type="text" name="min-value-money" 
                                                        class="input-disabled form-control min-value-money" 
                                                        disabled="disabled" value=""/> 
                                                <input type="hidden" name="min-value" class="form-control min-value" 
                                                        value=""/> 
                                            </div> 
                                        </div> 
                                        <div class="col-lg-6"> 
                                            <label class="lb-slider">To</label> 
                                            <div class="form-group"> 
                                                <input type="text" name="max-value-money" 
                                                        class="input-disabled form-control max-value-money" 
                                                        disabled="disabled" value=""/> 
                                                <input type="hidden" name="max-value" class="form-control max-value" 
                                                        value=""/> 
                                            </div> 
                                        </div> 
                                    </div> 
                                </div> 
                            </div>  --}}
                            <div class="buttons-filter">
                                {{-- <button class="btn btn-default" wire:click="search()" type="button">
                                    Apply filter
                                </button> --}}
                                <button class="btn btn-default" wire:click="clear()" type="button">
                                    Reset filter
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-9 col-md-12 col-sm-12 col-12 float-right">
                    <div class="content-page">
                        {{-- <div class="box-filters-job mt-15 mb-10">
                                <div class="row">
                                    <div class="col-lg-7">
                                            <span class="text-small">Showing <strong>41-60 </strong>of <strong>944
                                                </strong>jobs</span>
                                    </div>
                                    <div class="col-lg-5 text-lg-end mt-sm-15">
                                        <div class="display-flex2">
                                            <span class="text-sortby">Sort by:</span>
                                            <div class="dropdown dropdown-sort">
                                                <button class="btn dropdown-toggle" type="button" id="dropdownSort"
                                                        data-bs-toggle="dropdown" aria-expanded="false"
                                                        data-bs-display="static"><span>Newest Job</span> <i
                                                        class="fi-rr-angle-small-down"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-light"
                                                    aria-labelledby="dropdownSort">
                                                    <li><a class="dropdown-item active" href="#">Newest Jobs</a></li>
                                                    <li><a class="dropdown-item" href="#">Oldest Jobs</a></li> 
                                                </ul> 
                                            </div>
                                        </div> 
                                    </div> 
                                </div> 
                        </div>  --}}
                        <div class="row job-listing-grid-2">
                            @forelse($this->jobs() as $job)
                                <div class="col-xl-4 col-md-6 mb-30">
                                    <div class="card-grid-2 hover-up wow animate__animated animate__fadeIn h-100 d-flex flex-column"
                                        data-wow-delay=".{{ $loop->index * 0.1 }}s">
                                        <div class="card-block-info d-flex flex-column h-100">
                                            <div class="row">
                                                <div class="col-lg-12 col-12">
                                                    <a href="{{ route('jobs.show', $job->slug) }}"
                                                        class="card-2-img-text card-grid-2-img-medium">
                                                        <span class="card-grid-2-img-small">
                                                            <img loading="lazy"
                                                                alt="{{ $job->title ?? 'Dubai Job Finder' }}"
                                                                src="{{ $job->employer->logo ? Storage::url($job->employer->logo) : Storage::url($generalSettings->site_favicon) }}" />
                                                        </span>
                                                        <span>{{ $job->title }}</span>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="mt-15 text-sm text-mutted-2 d-flex align-items-center">
                                                <span class="flex-shrink-1 text-truncate"
                                                    style="min-width:0; max-width:50%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                    <i class="fi-rr-marker"></i>
                                                    {{ $job->location?->name }}
                                                </span>
                                                <span class="flex-shrink-1 text-truncate ms-3"
                                                    style="min-width:0; max-width:50%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                    <i class="fi-rr-briefcase"></i>
                                                    {{ $job->job_type->getLabel() }}
                                                </span>
                                            </div>

                                            <div class="text-small mt-15">
                                                {{ Str::limit(html_entity_decode(strip_tags($job->description)), 150) }}
                                                {{-- {!! Str::excerpt($job->description) !!} --}}
                                            </div>

                                            <div class="card-2-bottom mt-auto pt-3">
                                                <div class="row">
                                                    {{-- <div class="col-lg-6 col-8">
                                                        <span
                                                            class="text-brand-10 text-icon-first">{{ $job->employer->name }}</span>
                                                    </div>
                                                    <div class="col-lg-6 col-4 text-end"> --}}
                                                    <span class="card-text-price">
                                                        {{ $job->salary_range }}<span><span> AED/Year</span>
                                                            {{-- </div> --}}
                                                </div>
                                            </div>
                                            {{-- <div class="card-2-bottom mt-30">
                                                <div class="row">
                                                    <div class="col-lg-6 col-8">
                                                        <span
                                                            class="text-brand-10 text-icon-first">{{ $job->employer->name }}</span>
                                                    </div>
                                                    <div class="col-lg-6 col-4 text-end">
                                                        <span class="card-text-price">
                                                            {{ $job->salary_range }}<span> AED/Year</span> </span>
                                                    </div>
                                                </div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="card-grid-2 hover-up wow animate__animated animate__fadeIn">
                                        <div class="card-block-info text-center py-5">
                                            <div class="mb-3">
                                                <i class="fi-rr-search-alt text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5>No jobs found</h5>
                                            <p class="text-muted">We couldn't find any jobs matching your criteria. Try
                                                adjusting your filters or clearing them.</p>
                                            <div class="mt-4">
                                                <button wire:click="clear" class="btn btn-default">Clear all
                                                    filters</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        {{ $this->jobs()->links() }}
                    </div>
                </div>
            </div>
            <div class="row">
                @php
                    // $selectedCategory = $category ? \App\Models\JobCategory::find($category)?->name : null;
                    $selectedCategory = $categories[$category] ?? null;
                @endphp

                <livewire:pages.components.job-listing-content :category="$selectedCategory" :location="\App\Models\Location::find($location)?->name" :key="'job-content-' . md5(($location ?? 'all') . '-' . ($selectedCategory ?? 'all'))" />
            </div>
        </div>
    </section>
    {{-- <div class="section-box"> 
        <div class="container"> 
            <ul class="list-partners"> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay="0s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/samsung.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".1s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/google.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".2s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/facebook.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".3s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/pinterest.svg"/></figure>}
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".4s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/avaya.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".5s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/forbes.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".1s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/avis.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".2s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/nielsen.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".3s"> 
                    <a href="#"> 
                        <figure><img loading="lazy" alt="jobhub" src="assets/imgs/jobs/logos/doordash.svg"/></figure> 
                    </a> 
                </li> 
            </ul> 
        </div> 
    </div>  --}}
    @push('js')
        <script>
            document.addEventListener('livewire:initialized', () => {
                function initSelect2() {
                    $('#location-select').select2({
                        placeholder: 'Select Location',
                        allowClear: true
                    }).on('change', function(e) {
                        @this.set('location', $(this).val());
                    });
                    $('#category-select').select2({
                        placeholder: 'Select Category',
                        allowClear: true
                    }).on('change', function(e) {
                        @this.set('category', $(this).val());
                    });
                }

                initSelect2();

                Livewire.on('url-updated', (data) => {
                    history.pushState(null, '', data[0].url);
                });

                Livewire.on('seo-updated', (data) => {
                    const payload = data[0] || {};

                    if (payload.title) {
                        document.title = payload.title;
                    }

                    if (payload.description) {
                        let metaDescription = document.querySelector('meta[name="description"]');

                        if (!metaDescription) {
                            metaDescription = document.createElement('meta');
                            metaDescription.setAttribute('name', 'description');
                            document.head.appendChild(metaDescription);
                        }

                        metaDescription.setAttribute('content', payload.description);
                    }
                });

                Livewire.on('reset-select2', () => {
                    // clear select2 selections visually and notify Livewire
                    $('#location-select').val(null).trigger('change');
                    $('#category-select').val(null).trigger('change');
                });
            });
        </script>
    @endpush
</div>
