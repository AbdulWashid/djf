<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Blog\Post;
use App\Models\Blog\Category;
use Illuminate\Support\Facades\DB;

new #[Layout('components.frontend.main')] class extends Component {
    public $post;
    public $slug;
    public $relatedPosts = [];
    public $isPreview;
    public $recentPosts = [];
    public $categories = [];
    public $popularTags = [];

    public function mount($slug)
    {
        $this->slug = $slug;

        // Redirect if slug has trailing slash
        if (substr($this->slug, -1) === '/') {
            return redirect()->to(rtrim(request()->path(), '/'), 301);
        }

        $this->loadPost();
        $this->loadSidebarData();

    }

    protected function loadPost()
    {
        // Check if this is a preview request and assign to class property
        $this->isPreview = request()->has('preview') && request()->has('token');

        if ($this->isPreview) {
            // Load post without published restriction for preview
            $this->post = Post::with(['category', 'author', 'tags', 'media'])
                ->where('slug', $this->slug)
                ->firstOrFail();

            // Verify preview token
            $expectedToken = hash('sha256', $this->post->id . config('app.key'));
            if (request('token') !== $expectedToken) {
                abort(403, 'Invalid preview token');
            }

            // Don't track views for preview
        } else {
            // Normal published post loading
            $this->post = Post::with(['category', 'author', 'tags', 'media'])
                ->where('slug', $this->slug)
                ->published()
                ->firstOrFail();

            // Track view for published posts only
            $this->post->trackView();
        }

        // Load related/navigation posts
        $this->relatedPosts = $this->post->getRelatedPosts(4);

        // Set SEO metadata
        view()->share('canonical', $this->post->getCanonicalUrl());
        view()->share('metaTitle', $this->post->meta_title ?: $this->post->title);
        view()->share('metaDescription', $this->post->meta_description ?: $this->post->content_overview);

        view()->share('twitterTags', $this->post->twitter_tags);
        view()->share('ogTags', $this->post->og_tags);
        view()->share('faqSchema', $this->generateFaqSchema());
    }

    protected function loadSidebarData()
    {
        $this->recentPosts = rememberIfEnabled('recent_posts', now()->addMinutes(30), function () {
            return Post::published()
                ->where('id', '!=', $this->post->id)
                ->select(['id', 'title', 'slug', 'blog_author_id', 'blog_category_id', 'published_at', 'content_overview'])
                ->with(['category:id,name,slug', 'media', 'author'])
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get();
        });

        $this->categories = rememberIfEnabled('active_categories', now()->addMinutes(30), function () {
            return Category::active()
                ->withCount([
                    'posts' => function ($query) {
                        $query->published();
                    },
                ])
                ->having('posts_count', '>', 0)
                ->orderBy('name')
                ->get();
        });

        // Get popular tags
        $locale = app()->getLocale();
        $this->popularTags = rememberIfEnabled('popular_tags_' . $locale, now()->addHours(6), function () use ($locale) {
            // Use a more efficient query with proper indexing
            $rawTags = DB::table('taggables')
                ->join('tags', 'taggables.tag_id', '=', 'tags.id')
                ->join('blog_posts', function ($join) {
                    $join->on('taggables.taggable_id', '=', 'blog_posts.id')->where('taggables.taggable_type', Post::class);
                })
                ->where('blog_posts.status', 'published')
                ->where('blog_posts.published_at', '<=', now())
                ->select(['tags.id', 'tags.name', DB::raw('COUNT(*) as count')])
                ->groupBy('tags.id', 'tags.name')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            return $rawTags
                ->map(function ($tag) use ($locale) {
                    $name = $tag->name;

                    if (isset($name[0]) && $name[0] === '{') {
                        try {
                            $decoded = json_decode($name, true, 512, JSON_THROW_ON_ERROR);
                            $name = $decoded[$locale] ?? (reset($decoded) ?? $name);
                        } catch (\JsonException $e) {
                            // Fallback to original name if JSON parsing fails
                        }
                    }

                    return [
                        'name' => $name,
                        'count' => $tag->count,
                    ];
                })
                ->toArray();
        });
    }

    protected function generateFaqSchema()
    {
        $faqs = $this->post->faqs;

        if (!$faqs) {
            return null;
        }
        $mainEntity = [];
        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($faq['answer']),
                ],
            ];
        }
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
        return json_encode($schema);
    }

    // Generate Schema.org structured data
    protected function generateSchemaData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $this->post->title,
            'description' => $this->post->meta_description ?: $this->post->content_overview,
            'image' => $this->post->hasLargeImage() ? $this->post->getLargeImageUrl() : null,
            'datePublished' => $this->post->published_at->toIso8601String(),
            'dateModified' => $this->post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $this->post->author->name ?? 'Anonymous',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('path/to/your/logo.png'),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->post->getCanonicalUrl(),
            ],
        ];
    }

    // Share post to social media
    public function sharePost($platform)
    {
        $url = urlencode($this->post->getCanonicalUrl());
        $title = urlencode($this->post->title);

        switch ($platform) {
            case 'twitter':
                return redirect()->away("https://twitter.com/intent/tweet?url={$url}&text={$title}");
            case 'facebook':
                return redirect()->away("https://www.facebook.com/sharer/sharer.php?u={$url}");
            case 'linkedin':
                return redirect()->away("https://www.linkedin.com/sharing/share-offsite/?url={$url}");
            case 'whatsapp':
                return redirect()->away("https://api.whatsapp.com/send?text={$title}%20{$url}");
        }
    }

    // Provide layout variables to the #[Layout] attribute natively
    public function layoutData(): array
    {
        $schemaJson = json_encode($this->generateSchemaData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return [
            'pageType' => 'blog_post',
            'postTitle' => $this->post->title ?? '',
            'postCategory' => $this->post->category->name ?? '',
            'authorName' => $this->post->author->name ?? '',
            'publishDate' => $this->post->published_at,
            'pageDescription' => $this->post->meta_description ?: $this->post->content_overview,
            'metaKeywords' => $this->post->meta_keywords ?? '',
            'ogTags' => $this->post->og_tags ?? '',
            'twitterTags' => $this->post->twitter_tags ?? '',
            'canonicalUrl' => $this->post ? $this->post->getCanonicalUrl() : '',
            'ogImage' => ($this->post && $this->post->hasLargeImage()) ? $this->post->getLargeImageUrl() : null,
            'schemaData' => $schemaJson,
        ];
    }

    // Use with() for Volt dynamic rendering variables
    public function with(): array
    {
        $schemaJson = json_encode($this->generateSchemaData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return [
            'faqSchema' => $this->generateFaqSchema(),
            'schemaData' => $schemaJson,
        ];
    }
}; ?>

<div>
    @if (isset($schemaData))
        @push('js')
            <script type="application/ld+json">
                {!! $schemaData !!}
            </script>
        @endpush
    @endif

    {{-- Preview Banner --}}
    @if ($isPreview)
        <div
            class="fixed top-0 left-0 right-0 z-50 px-4 py-2 font-semibold text-center text-white bg-orange-500 shadow-lg">
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-eye"></i>
                <span>Preview Mode - This is how your blog post will look when published</span>
                <button onclick="window.close()"
                    class="px-3 py-1 ml-4 transition-colors rounded bg-white/20 hover:bg-white/30">
                    <i class="mr-1 fa-solid fa-times"></i> Close Preview
                </button>
            </div>
        </div>
        <div class="h-12"></div> {{-- Spacer for fixed banner --}}
    @endif

    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>
                @php
                    $cat_items = [
                        ['label' => 'Blog', 'url' => route('blog')],
                        //            ['label' => $post->category->name, 'url' => route('blog', ['category' => $post->category->id])],
                        ['label' => $post->title],
                    ];
                    $pg_title = $post->title;
                @endphp

                @foreach ($cat_items as $item)
                    @if (isset($item['url']))
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @else
                        <li>{{ $item['label'] }}</li>
                    @endif
                @endforeach
                @if (count($cat_items) === 0)
                    <li>{{ $pg_title }}</li>
                @endif
            </ul>
        </div>
    </div>
    <div class="archive-header pt-50 pb-50 text-center">
        <div class="container">
            <h1 class="h1 mb-30 text-center w-75 mx-auto">
                {{ $pg_title }}
            </h1>
            <div class="post-meta text-muted d-flex align-items-center mx-auto justify-content-center">
                <div class="author d-flex align-items-center mr-30">
                    @if ($post->author && $post->author->profile_photo_path)
                        <img src="{{ Storage::url($post->author->profile_photo_path) }}" alt="{{ $post->author->name }}"
                            width="30" height="30" class="rounded-[50%]" />
                    @else
                        <img src="https://placehold.co/45x45?text={{ substr($post->author->name ?? 'A', 0, 1) }}"
                            alt="{{ $post->author->name ?? 'Author' }}" width="30" height="30"
                            class="rounded-[50%]" />
                    @endif
                    <span>{{ $post->author->name ?? 'Anonymous' }}</span>
                </div>
                <div class="date mr-30">
                    <span><i class="fi-rr-edit mr-5 text-grey-6"></i>
                        {{ $post->published_at->format('M d, Y') }}</span>
                </div>
                @if ($post->category)
                    <div>
                        <a href="{{ route('blog', ['category' => $post->category->id]) }}"
                            class="rounded-[50px] bg-color-black/5 px-[26px] py-1.5 text-black/60 hover:bg-color-blue hover:text-white">
                            {{ $post->category->name }}
                        </a>
                    </div>
                @endif
                @if ($post->is_featured)
                    <div>
                        <span class="rounded-[50px] bg-orange-500 px-[26px] py-1.5 text-white">
                            Featured
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="post-loop-grid">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="single-body">
                        <figure class="mb-30">
                            <a href="{{ $post->getUrl() }}" wire:click="trackView('{{ $post->id }}')">
                                @if ($post->hasLargeImage())
                                    <img src="{{ $post->getLargeImageUrl() }}" alt="{{ $post->title }}" />
                                @else
                                    <img src="https://placehold.co/1600x900?text={{ $post->title }}"
                                        alt="{{ $post->title }}" />
                                @endif
                            </a>
                        </figure>
                        <div class="excerpt mb-30">
                            <p> {{ $post->content_overview }}</p>
                        </div>
                        <div class="single-content">
                            {!! $post->content_raw !!}
                        </div>

                        {{-- <div class="author-bio p-30 mt-50 border-radius-15 bg-white"> 
                            <div class="author-image mb-15"> 
                                <a href="author.html">
                                    <img src="assets/imgs/avatar/ava_14.png" alt="" class="avatar">
                                </a> 
                                <div class="author-infor"> 
                                    <h5 class="mb-5">Steven Job</h5> 
                                    <p class="mb-0 text-muted font-xs"> 
                                        <span class="mr-10">306 posts</span> 
                                        <span class="has-dot">Since 2012</span> 
                                    </p> 
                                </div> 
                            </div> 
                            <div class="author-des"> 
                                <p>
                                    Hi, I'm a recruiter with over 25 years of experience. I have worked in many 
                                    multinational companies and corporations. With my experiences, I hope my articles 
                                    will bring you knowledge and inspiration.
                                </p> 
                            </div> 
                        </div>  --}}

                        @if ($post->faqs)

                            <div class="row mt-50">
                                <div class="col-lg-12">
                                    <h2 class="heading-border"><span>{{ $post->faq_title }}</span></h2>
                                    <div class="accordion accordion-flush">
                                        @php
                                            $fqlist = [];
                                        @endphp
                                        @forelse($post->faqs as $key=> $faq)
                                            @php
                                                $fqlist[$faq['question']] = strip_tags($faq['answer']);
                                            @endphp
                                            <div class="accordion-item">
                                                <p class="accordion-header" id="flush-headingOne2">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#flush-collapseOne{{ $key }}"
                                                        aria-expanded="false"
                                                        aria-controls="flush-collapseOne{{ $key }}">
                                                        {{ $faq['question'] }}
                                                    </button>
                                                </p>
                                                <div id="flush-collapseOne{{ $key }}"
                                                    class="accordion-collapse collapse"
                                                    aria-labelledby="flush-headingOne"
                                                    data-bs-parent="#accordionFlushExample2">
                                                    <div class="accordion-body">
                                                        <div class="mb-15">
                                                            {!! $faq['answer'] !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <h5 class="text-center">No FAQs found</h5>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @if (isset($faqSchema))
                                @push('js')
                                    <script type="application/ld+json">
                                        {!! $faqSchema !!}
                                    </script>
                                @endpush
                            @endif
                        @endif

                        @if (count($relatedPosts) > 0)
                            <div class="related-posts mt-50">
                                <h4 class="mb-30">Related Posts</h4>
                                <div class="box-swiper">
                                    <div class="swiper-container swiper-group-3">
                                        <div class="swiper-wrapper pb-30 pt-5">
                                            @foreach ($relatedPosts as $related)
                                                <div class="swiper-slide">
                                                    <div class="card-grid-3 hover-up p-15">
                                                        <a href="{{ $related->getUrl() }}">
                                                            <figure class="thumb mr-15">
                                                                @if ($related->hasLargeImage())
                                                                    <img src="{{ $related->getLargeImageUrl() }}"
                                                                        alt="{{ $related->title }}" />
                                                                @else
                                                                    <img src="https://placehold.co/1600x900?text={{ substr($related->title, 0, 10) }}"
                                                                        alt="{{ $related->title }}" />
                                                                @endif
                                                            </figure>
                                                        </a>
                                                        <h6 class="heading-md mt-15 mb-0"><a
                                                                href="{{ $related->getUrl() }}">{{ Str::limit($related->title, 50) }}</a>
                                                        </h6>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-pagination swiper-pagination3"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-sm-12 col-12 pl-40 pl-lg-15 mt-lg-30">
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
                                        <h5 class="h5"><a href="{{ $recentPost->getUrl() }}"
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
                    <div class="sidebar-shadow widget-categories">
                        <h5 class="sidebar-title">Category</h5>
                        <ul>
                            @foreach ($categories as $category)
                                <li class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('blog', ['category' => $category->id]) }}"
                                        class="w-full text-left">
                                        {{ $category->name }}
                                    </a>
                                    <span>{{ $category->posts_count }}</span>
                                </li>
                            @endforeach
                        </ul>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>