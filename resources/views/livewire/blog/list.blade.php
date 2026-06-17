<!-- Content -->
<div>
    @php
        $breadcrumbTitle = 'Blog';
        $breadcrumbItems = [];

        if ($search) {
            $breadcrumbTitle = 'Search results for "' . $search . '"';
            $breadcrumbItems = [['label' => 'Blog', 'url' => route('blog')], ['label' => 'Search']];
        } elseif ($activeCategory) {
            $activeCategoryName = $categories->firstWhere('id', $activeCategory)?->name;
            $breadcrumbTitle = $activeCategoryName ? $activeCategoryName . ' Articles' : 'Blog';
            $breadcrumbItems = [
                ['label' => 'Blog', 'url' => route('blog')],
                ['label' => $activeCategoryName ?? 'Category'],
            ];
        } elseif ($featuredOnly) {
            $breadcrumbTitle = 'Featured Blog Posts';
            $breadcrumbItems = [['label' => 'Blog', 'url' => route('blog')], ['label' => 'Featured']];
        }
    @endphp
    <style>
        .blog-list-image img {
            width: 100%;
            height: 100%;
            object-fit: fill;
        }
    </style>
    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('blog') }}">Blogs</a></li>
                @php
                    $cat_items = [];
                    $pg_title = 'Blog';

                    if ($search) {
                        $pg_title = 'Search results for "' . $search . '"';
                        $cat_items = [['label' => 'Search']];
                    } elseif ($activeCategory) {
                        $activeCategoryName = $categories->firstWhere('id', $activeCategory)?->name;
                        $pg_title = $activeCategoryName ? $activeCategoryName . ' Articles' : 'Blog';
                        $cat_items = [['label' => $activeCategoryName ?? 'Category']];
                    } elseif ($featuredOnly) {
                        $pg_title = 'Featured Blog Posts';
                        $cat_items = [['label' => 'Featured']];
                    }
                @endphp

                @foreach ($cat_items as $item)
                    @if (isset($item['url']))
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @else
                        <li>{{ $item['label'] }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div class="archive-header pt-50 pb-50 text-center">
        <div class="container">
            <h1 class="h1 mb-30 text-center w-75 mx-auto">
                {{ $pg_title }}
            </h1>
        </div>
    </div>
    <div class="post-loop-grid">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="post-listing">
                        <div
                            class="flex flex-col items-start justify-between p-4 mb-6 bg-white rounded-lg shadow-sm sm:flex-row sm:items-center">
                            <!-- Search Status -->
                            <div class="mb-3 sm:mb-0">
                                @if ($search)
                                    <div class="text-gray-700">
                                        <span class="font-medium">Search results for:</span>
                                        <span
                                            class="px-2 py-1 rounded bg-color-blue/10 text-primary-600">{{ $search }}</span>

                                        @if ($posts->total() > 0)
                                            <span class="ml-2 text-gray-600">
                                                ({{ $posts->total() }} {{ Str::plural('result', $posts->total()) }})
                                            </span>
                                        @else
                                            <span class="ml-2 text-gray-600">
                                                (No results found)
                                            </span>
                                        @endif

                                        <button wire:click="clearSearch" class="ml-2 text-primary-600 hover:underline">
                                            Clear
                                        </button>
                                    </div>
                                @elseif($activeCategory)
                                    <div class="text-gray-700">
                                        <span class="font-medium">Category:</span>
                                        <span class="px-2 py-1 rounded bg-color-blue/10 text-primary-600">
                                            {{ $categories->firstWhere('id', $activeCategory)?->name ?? 'Selected Category' }}
                                        </span>

                                        <button wire:click="filterByCategory(null)"
                                            class="ml-2 text-primary-600 hover:underline">
                                            Clear
                                        </button>
                                    </div>
                                @elseif($featuredOnly)
                                    <div class="text-gray-700">
                                        <span class="font-medium">Showing:</span>
                                        <span class="px-2 py-1 bg-warning rounded">
                                            Featured Posts
                                        </span>

                                        <button wire:click="toggleFeatured"
                                            class="ml-2 text-primary-600 hover:underline">
                                            Show All
                                        </button>
                                    </div>
                                @else
                                    <h3 class="">All Posts</h3>
                                @endif
                            </div>

                            <!-- Sort Controls -->
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600">Sort by:</span>
                                <div class="flex overflow-hidden border rounded">
                                    <button wire:click="sortBy('published_at')"
                                        class="flex items-center px-3 py-1.5 text-sm {{ $sortField === 'published_at' ? 'btn-primary' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Latest
                                        @if ($sortField === 'published_at')
                                            <i
                                                class="fi-ts-angle-small-{{ $sortDirection === 'desc' ? 'down' : 'up' }} ml-1 text-xs"></i>
                                        @endif
                                    </button>
                                    <button wire:click="sortBy('view_count')"
                                        class="flex items-center px-3 py-1.5 text-sm border-l {{ $sortField === 'view_count' ? 'btn-primary' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                        Popular
                                        @if ($sortField === 'view_count')
                                            <i
                                                class="fi-ts-angle-small-{{ $sortDirection === 'desc' ? 'down' : 'up' }} ml-1 text-xs"></i>
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                        @forelse($posts as $post)
                            <div class="card mb-3 hover-up wow animate__animated animate__fadeIn" data-wow-delay=".0s"
                                wire:key="post-{{ $post->id }}"
                                @if ($post->is_featured) style="border-color: #FFC107;" @endif>
                                <div class="row g-0">

                                    <div class="col-md-5">
                                        <a href="{{ $post->getUrl() }}" wire:click="trackView('{{ $post->id }}')">

                                            <picture class="blog-list-image">
                                                <source media="(min-width: 768px)"
                                                    srcset="{{ $post->hasMediumImage()
                                                        ? $post->getMediumImageUrl()
                                                        : 'https://placehold.co/300x400?text=' . urlencode($post->title) }}">

                                                <img src="{{ $post->hasLargeImage()
                                                    ? $post->getLargeImageUrl()
                                                    : 'https://placehold.co/1600x900?text=' . urlencode($post->title) }}"
                                                    alt="{{ $post->title }}" class="img-fluid w-100 rounded">
                                            </picture>

                                        </a>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="card-body">
                                            <h5 class="post-title mb-1">
                                                <a href="{{ $post->getUrl() }}"
                                                    wire:click="trackView('{{ $post->id }}')">
                                                    {{ $post->title }}
                                                </a>
                                            </h5>
                                            <div class="post-meta text d-flex align-items-center mb-15">
                                                <div class="author d-flex align-items-center mr-30">
                                                    @if ($post->author && $post->author->profile_photo_path)
                                                        <img src="{{ Storage::url($post->author->profile_photo_path) }}"
                                                            alt="{{ $post->author->name }}" width="45"
                                                            height="45" class="rounded-[50%]" />
                                                    @else
                                                        <img src="https://placehold.co/45x45?text={{ substr($post->author->name ?? 'A', 0, 1) }}"
                                                            alt="{{ $post->author->name ?? 'Author' }}" width="25"
                                                            height="25" class="rounded-[50%]" />
                                                    @endif
                                                    <span>{{ $post->author->name ?? 'Anonymous' }}</span>
                                                </div>
                                                <div class="date">
                                                    <span>
                                                        <i class="fi-rr-edit mr-5 text-grey-6"></i>
                                                        {{ $post->published_at->format('M d, Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="post-excerpt text d-none d-md-block line-clamp-2">
                                                {{ $post->content_overview }}
                                            </p>
                                            <div class="card-2-bottom mt-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="keep-reading">
                                                        @if ($post->category)
                                                            <a href="#"
                                                                wire:click.prevent="filterByCategory('{{ $post->category->id }}')"
                                                                class="btn btn-tags-sm mb-10 mr-5">
                                                                {{ $post->category->name }}
                                                            </a>
                                                        @else
                                                            <span
                                                                class="btn btn-tags-sm mb-10 mr-5">Uncategorized</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- No Posts Found Message -->
                            <div class="w-full p-10 text-center bg-white rounded-[10px] shadow-sm">
                                <div class="flex flex-col items-center justify-center py-12">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4 text-gray-300"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <p class="text-lg font-medium text-gray-500">No articles found</p>
                                    <p class="mb-6 text-gray-400">Try changing your search criteria</p>
                                    <button wire:click="clearFilters"
                                        class="inline-block rounded-[50px] bg-color-blue px-6 py-3 text-white hover:bg-blue-700 transition">
                                        View All Posts
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($posts->hasPages())
                        <div class="paginations wow animate__animated animate__fadeIn">
                            <ul class="pager">
                                <li>
                                    <button wire:click="previousPage"
                                        @if (!$posts->onFirstPage()) wire:loading.attr="disabled" @endif
                                        @if ($posts->onFirstPage()) disabled @endif
                                        class="pager-prev  {{ $posts->onFirstPage() ? 'active' : '' }}">
                                    </button>
                                </li>
                                @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                                    <li>
                                        <button wire:click="gotoPage({{ $page }})"
                                            class="pager-number {{ $page == $posts->currentPage() ? 'active' : '' }} ">{{ $page }}</button>
                                    </li>
                                @endforeach

                                <li>
                                    <button wire:click="nextPage"
                                        @if (!$posts->hasMorePages()) wire:loading.attr="disabled" @endif
                                        @if (!$posts->hasMorePages()) disabled @endif
                                        class="pager-next  {{ !$posts->hasMorePages() ? 'active' : '' }} "></button>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4 col-md-12 col-sm-12 col-12 pl-40 pl-lg-15 mt-lg-30">
                    <div class="widget_search mb-40">
                        <div class="search-form">
                            <form wire:submit.prevent="$refresh" role="search" aria-labelledby="search-heading">
                                <input id="blog-search-input" type="search" wire:model.debounce.500ms="search"
                                    placeholder="Type to search..."
                                    class="h-full w-full rounded-[50px] border border-[#E1E1E1] bg-white py-[15px] pl-16 pr-12 text-lg text-color-black outline-none transition-all placeholder:text-color-black focus:border-color-blue focus:ring-2 focus:ring-color-blue/20"
                                    aria-describedby="search-description" />
                                <button type="submit"><i class="fi-rr-search"></i></button>
                                <div wire:loading wire:target="search" class="ml-2 text-primary-600"
                                    aria-live="polite">
                                    <span class="sr-only">Searching...</span>
                                    <svg class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </form>
                            @if ($search)
                                <div class="flex items-center justify-between mt-2 text-sm text-gray-600"
                                    aria-live="polite">
                                    <div>
                                        @if ($posts->total() > 0)
                                            Found {{ $posts->total() }} {{ Str::plural('result', $posts->total()) }}
                                            for
                                            "{{ $search }}"
                                        @else
                                            No results found for "{{ $search }}"
                                        @endif
                                    </div>
                                    <button wire:click="clearSearch"
                                        class="px-2 rounded text-primary-600 hover:underline focus:outline-none focus:ring-2 focus:ring-color-blue/40">
                                        Clear search
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="sidebar-shadow widget-categories">
                        <h5 class="sidebar-title">Category</h5>

                        <div class="form-group select-style select-style-icon" wire:ignore>
                            <select id="category-select" class="form-control form-icons">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ $activeCategory == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fi-rr-list"></i>
                        </div>

                        <div class="mt-3">
                            <button wire:click="toggleFeatured"
                                class="w-full text-left flex items-center {{ $featuredOnly ? 'text-primary-600 font-semibold' : 'hover:text-primary-600' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polygon
                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                                    </polygon>
                                </svg>
                                Featured Only
                                <span wire:loading.delay wire:target="toggleFeatured" class="ml-2 text-primary-600">
                                    <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2-647z">
                                        </path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="sidebar-shadow sidebar-news-small">
                        <h5 class="sidebar-title">Latest news</h5>
                        <div class="post-list-small">
                            @foreach ($recentPosts as $recentPost)
                                <div class="post-list-small-item d-flex align-items-center">
                                    <figure class="thumb mr-15">
                                        @if ($recentPost->hasLargeImage())
                                            <img src="{{ $recentPost->getLargeImageUrl() }}"
                                                alt="{{ $recentPost->title }}" />
                                        @else
                                            <img src="https://placehold.co/600x600?text={{ substr($recentPost->title, 0, 10) }}"
                                                alt="{{ $recentPost->title }}" />
                                        @endif
                                    </figure>
                                    <div class="content">
                                        <h5><a href="{{ $recentPost->getUrl() }}"
                                                wire:click="trackView('{{ $recentPost->id }}')">{{ Str::limit($recentPost->title, 50) }}</a>
                                        </h5>
                                        <div class="post-meta text d-flex align-items-end flex-column">
                                            <div class="author">
                                                <span>{{ $recentPost->author->name ?? 'Anonymous' }}</span>
                                            </div>

                                            <div class="date">
                                                <span>{{ $recentPost->published_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="sidebar-shadow">
                        <h5 class="sidebar-title">Popular Tags</h5>
                        <div class="block-tags">

                            @forelse($popularTags as $tag)
                                <button wire:click="searchByTag('{{ is_object($tag) ? $tag->name : $tag['name'] }}')"
                                    class="btn btn-tags-sm mb-10 mr-5">

                                    <span>
                                        @if (is_object($tag) && isset($tag->name) && is_string($tag->name))
                                            {{ $tag->name }}
                                        @elseif(is_object($tag) && isset($tag->name) && is_object($tag->name))
                                            {{ $tag->name->{app()->getLocale()} ?? '' }}
                                        @elseif(is_array($tag) && isset($tag['name']) && is_string($tag['name']))
                                            {{ $tag['name'] }}
                                        @elseif(is_array($tag) && isset($tag['name']) && is_array($tag['name']))
                                            {{ $tag['name'][app()->getLocale()] ?? '' }}
                                        @else
                                            {{ is_string($tag) ? $tag : json_encode($tag) }}
                                        @endif
                                    </span>

                                </button>

                            @empty
                                No tags found
                            @endforelse

                            @if ($search && count($popularTags) > 0)
                                <div class="mt-4 text-sm text-gray-600">
                                    <button wire:click="clearSearch"
                                        class="flex items-center text-primary-600 hover:underline">
                                        <i class="mr-1 text-xs fa-solid fa-arrow-left"></i>
                                        View all tags
                                    </button>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @push('js')
        <script>
            document.addEventListener('livewire:initialized', () => {
                function initCategorySelect() {
                    if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;
                    const select = $('#category-select');

                    if (select.hasClass('select2-hidden-accessible')) {
                        select.select2('destroy');
                    }

                    select.select2({
                        placeholder: 'Select Category',
                        allowClear: true,
                        width: '100%'
                    }).off('change.blogCategory').on('change.blogCategory', function() {
                        @this.call('filterByCategory', $(this).val() || null);
                    });
                }

                initCategorySelect();

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
                    $('#category-select').val(null).trigger('change.select2');
                });
            });
        </script>
    @endpush

    <livewire:subscribe />
</div>
<!-- End Content -->
