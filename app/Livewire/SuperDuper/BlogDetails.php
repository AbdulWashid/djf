<?php

namespace App\Livewire\SuperDuper;

use App\Models\Blog\Post;
use App\Models\Blog\Category;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class BlogDetails extends Component
{
    public $post;
    public $slug;
    public $relatedPosts = [];
    public $previousPost;
    public $isPreview;
    public $nextPost;
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
        // Check if this is a preview request
        $isPreview = request()->has('preview') && request()->has('token');

        if ($isPreview) {
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
        $this->relatedPosts = $this->post->getRelatedPosts(6);
        $this->previousPost = $this->post->getPreviousPost();
        $this->nextPost = $this->post->getNextPost();

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
                ->select(['id', 'title', 'slug', 'blog_category_id', 'published_at', 'content_overview'])
                ->with([
                    'category:id,name,slug',
                    'media' => function ($query) {
                        $query->where('collection_name', 'featured');
                    },
                ])
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
            'image' => $this->post->hasFeaturedImage() ? $this->post->getFeaturedImageUrl('large') : null,
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

    public function render()
    {
        return view('livewire.blog.detail', [
            'post' => $this->post,
            'previousPost' => $this->previousPost,
            'nextPost' => $this->nextPost,
            'relatedPosts' => $this->relatedPosts,
            'recentPosts' => $this->recentPosts,
            'categories' => $this->categories,
            'popularTags' => $this->popularTags,
            'isPreview' => $this->isPreview,
            'faqSchema' => $this->generateFaqSchema(),
        ])->layout('components.frontend.main', [
            'pageType' => 'blog_post',
            'postTitle' => $this->post->title,
            'postCategory' => $this->post->category->name ?? '',
            'authorName' => $this->post->author->name ?? '',
            'publishDate' => $this->post->published_at,
            'pageDescription' => $this->post->meta_description ?: $this->post->content_overview,
            'metaKeywords' => $this->post->meta_keywords,
            'ogTags' => $this->post->og_tags,
            'twitterTags' => $this->post->twitter_tags,
            'canonicalUrl' => $this->post->getCanonicalUrl(),
            'ogImage' => $this->post->hasFeaturedImage() ? $this->post->getFeaturedImageUrl('large') : null,
            'schemaData' => [$this->generateSchemaData()],
        ]);
    }
}
