<?php

namespace App\Livewire\SuperDuper;

use App\Models\Blog\Post;
use App\Models\Blog\Category;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class BlogList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $activeCategory = null;
    public $categories = [];
    public $featuredOnly = false;
    public $perPage = 10;
    public $sortField = 'published_at';
    public $sortDirection = 'desc';
    public $recentPosts = [];
    public $popularTags = [];
    public $isSearching = false;
    public $pageTitle = 'Blogs';
    public $pageDescription = 'Discover our latest insights, tips, and updates...';

    protected $queryString = [
        'search' => ['except' => ''],
        'activeCategory' => ['except' => null, 'as' => 'category'],
        'featuredOnly' => ['except' => false, 'as' => 'featured'],
        'sortField' => ['except' => 'published_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        // Get all active categories with post counts
        $this->categories = rememberIfEnabled('active_categories', now()->addMinutes(30), function () {
            return Category::active()
                ->withCount([
                    'posts' => function ($query) {
                        $query->published();
                    },
                ])
                // ->having('posts_count', '>', 0)
                ->orderBy('name')
                ->get();
        });

        // Get recent posts for sidebar
        $this->recentPosts = rememberIfEnabled('recent_posts', now()->addMinutes(30), function () {
            return Post::published()
                ->select(['id', 'title', 'slug', 'blog_author_id', 'blog_category_id', 'published_at', 'content_overview'])
                ->with([
                    'category:id,name,slug',
                    'media',
                    'author',
                ])
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get();
        });

        // Get popular tags
        $locale = app()->getLocale();
        $this->popularTags = rememberIfEnabled('popular_tags_' . $locale, now()->addHours(6), function () use ($locale) {
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

        if ($this->activeCategory) {
            $this->activeCategory = $this->categories->firstWhere('id', $this->activeCategory)?->id ?? null;
        }

        $this->syncSeo();
    }

    // Reset pagination when updating search
    public function updatingSearch()
    {
        $this->resetPage();
        $this->isSearching = true;
    }

    public function updatedSearch()
    {
        $this->syncSeo();
    }

    // Filter by category
    public function filterByCategory($categoryId = null)
    {
        $this->activeCategory = filled($categoryId) ? $categoryId : null;
        $this->resetPage();
        $this->syncSeo();

        if (blank($this->activeCategory)) {
            $this->dispatch('reset-select2');
        }
    }

    // Toggle featured posts filter
    public function toggleFeatured()
    {
        $this->featuredOnly = !$this->featuredOnly;
        $this->resetPage();
        $this->syncSeo();
    }

    // Sort posts by field
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
        $this->syncSeo();
    }

    // Track post views
    public function trackView($postId)
    {
        $post = Post::find($postId);
        if ($post) {
            $post->trackView();
        }
    }

    // Clear search
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
        $this->isSearching = false;
        $this->syncSeo();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->activeCategory = null;
        $this->featuredOnly = false;
        $this->sortField = 'published_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
        $this->isSearching = false;
        $this->syncSeo();
        $this->dispatch('reset-select2');
    }

    /**
     * Handle search via tag
     *
     * @param string|array|object $tagName
     * @return void
     */
    public function searchByTag($tagName)
    {
        // Handle different tag name formats
        if (is_string($tagName)) {
            $this->search = $tagName;
        } elseif (is_array($tagName) && isset($tagName['en'])) {
            $this->search = $tagName['en'];
        } elseif (is_object($tagName) && isset($tagName->en)) {
            $this->search = $tagName->en;
        } else {
            // Try to convert to string or use default
            $this->search = (string) $tagName;
        }

        $this->resetPage();
        $this->syncSeo();
    }

    private function syncSeo(): void
    {
        $categoryName = $this->activeCategory ? $this->categories->firstWhere('id', $this->activeCategory)?->name : null;

        if (!empty($this->search)) {
            $this->pageTitle = 'Search results for "' . $this->search . '" | Blogs';
            $this->pageDescription = 'Search results for "' . $this->search . '" on Blogs.';
        } elseif ($categoryName) {
            $this->pageTitle = $categoryName . ' Articles | Blogs';
            $this->pageDescription = 'Browse the latest ' . $categoryName . ' articles on Blogs.';
        } elseif ($this->featuredOnly) {
            $this->pageTitle = 'Featured Blog Posts | Blogs';
            $this->pageDescription = 'Explore featured blog posts, insights, tips, and updates on Blogs.';
        } else {
            $this->pageTitle = 'Blogs';
            $this->pageDescription = 'Discover our latest insights, tips, and updates...';
        }

        $this->dispatch('seo-updated', [
            'title' => $this->pageTitle,
            'description' => $this->pageDescription,
        ]);

        $this->dispatch('schema-updated', [
            'schemas' => $this->buildSchemas($categoryName),
        ]);
    }

    protected function buildSchemas(?string $categoryName = null): array
    {
        $breadcrumbItems = [['label' => 'Blog', 'url' => route('blog')]];

        if (!empty($this->search)) {
            $breadcrumbItems[] = ['label' => 'Search: ' . $this->search];
        } elseif (!empty($categoryName)) {
            $breadcrumbItems[] = ['label' => $categoryName];
        } elseif ($this->featuredOnly) {
            $breadcrumbItems[] = ['label' => 'Featured'];
        }

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => url('/'),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($breadcrumbItems)
                    ->values()
                    ->map(function (array $item, int $index) {
                        $entry = [
                            '@type' => 'ListItem',
                            'position' => $index + 1,
                            'name' => $item['label'],
                        ];

                        if (!empty($item['url'])) {
                            $entry['item'] = $item['url'];
                        }

                        return $entry;
                    })
                    ->all(),
            ],
        ];
    }

    public function render()
    {
        $query = Post::query()
            ->with(['category', 'author', 'media', 'tags'])
            ->published()
            ->locale(App::getLocale());

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('content_raw', 'like', $searchTerm)
                    ->orWhere('content_overview', 'like', $searchTerm)
                    ->orWhereHas('tags', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($this->featuredOnly) {
            $query->featured();
        }

        if ($this->activeCategory) {
            $query->where('blog_category_id', $this->activeCategory);
        }

        $posts = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.blog.list', [
            'posts' => $posts,
        ])->layout('components.frontend.main', [
            'pageType' => 'blog',
            'pageTitle' => $this->pageTitle,
            'pageDescription' => $this->pageDescription,
            'metaKeywords' => 'blog, articles, insights, technology news',
        ]);
    }
}
