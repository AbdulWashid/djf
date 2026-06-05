{{-- @dd($locations, $location, str_replace(' ', '-', strtolower($location)), str_replace(' ', '-', strtolower($locations[6]))) --}}
<div>
    <section class="section-box-2">
        <div class="box-head-single none-bg">
            <div class="container">
                <h1 class="h1">Currently available Jobs @if (!empty($location))
                        in {{ Str::ucwords($location) }}
                    @endif!</h1>
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
                                            <option value="{{ $loc }}"
                                                {{ Str::slug($location) == Str::slug($loc) ? 'selected' : '' }}>
                                                {{ Str::ucwords($loc) }}
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
                                <button class="btn btn-default" wire:click="search()" type="button">
                                    Apply filter
                                </button>
                                <button class="btn" wire:click="clear()">Reset filter</button>
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
                            @forelse($jobs as $job)
                                <div class="col-xl-4 col-md-6 mb-30">
                                    <div class="card-grid-2 hover-up wow animate__animated animate__fadeIn h-100 d-flex flex-column"
                                        data-wow-delay=".{{ $loop->index * 0.1 }}s">
                                        <div class="card-block-info d-flex flex-column h-100">
                                            <div class="row">
                                                <div class="col-lg-12 col-12">
                                                    <a href="{{ route('jobs.show', $job->slug) }}"
                                                        class="card-2-img-text card-grid-2-img-medium">
                                                        <span class="card-grid-2-img-small">
                                                            <img alt="{{ $job->title ?? 'Dubai Job Finder' }}"
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
                                                    {{ $job->location }}
                                                </span>
                                                <span class="flex-shrink-1 text-truncate ms-3"
                                                    style="min-width:0; max-width:50%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                    <i class="fi-rr-briefcase"></i>
                                                    {{ $job->job_type->getLabel() }}
                                                </span>
                                            </div>

                                            <div class="text-small mt-15">
                                                {!! Str::excerpt($job->description) !!}
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
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- <div class="section-box"> 
        <div class="container"> 
            <ul class="list-partners"> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay="0s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/samsung.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".1s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/google.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".2s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/facebook.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".3s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/pinterest.svg"/></figure>}
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".4s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/avaya.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".5s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/forbes.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".1s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/avis.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".2s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/nielsen.svg"/></figure> 
                    </a> 
                </li> 
                <li class="wow animate__animated animate__fadeInUp hover-up" data-wow-delay=".3s"> 
                    <a href="#"> 
                        <figure><img alt="jobhub" src="assets/imgs/jobs/logos/doordash.svg"/></figure> 
                    </a> 
                </li> 
            </ul> 
        </div> 
    </div>  --}}
</div>
