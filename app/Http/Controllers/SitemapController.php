<?php

namespace App\Http\Controllers;

use App\Models\Blog\Post;
use App\Models\Blog\Category;
use App\Models\JobCategory;
use App\Models\Opening;
use App\Settings\GeneralSettings;
use DB;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = Cache::remember('sitemap_xml', 3600, function () {
            return $this->generateSitemap();
        });

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function generateSitemap(): string
    {
        $urls = collect();

        // Add static pages
        $staticPages = [
            ['url' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => route('jobs'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => route('blog'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('contact-us'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls->push([
                'loc' => $page['url'],
                'lastmod' => now()->toISOString(),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ]);
        }


        $pages = \App\Models\StaticPage::select('slug','status','updated_at')->where('status', 1)->get();
        foreach ($pages as $page) {
            // $staticPages[] = [
            //     'url' => route('page', $page->slug),
            //     'priority' => '0.7',
            //     'changefreq' => 'monthly'
            // ];
            $urls->push([
                'loc' => route('page', $page->slug),
                'lastmod' => $page->updated_at->toISOString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ]);
        }

        // foreach ($staticPages as $page) {
        //     $urls->push([
        //         'loc' => $page['url'],
        //         'lastmod' => now()->toISOString(),
        //         'changefreq' => $page['changefreq'],
        //         'priority' => $page['priority'],
        //     ]);
        // }

        // Add job categories
        JobCategory::active()->select(['slug', 'updated_at'])
            ->chunk(100, function ($cats) use ($urls) {
                foreach ($cats as $cat) {
                    $urls->push([
                        'loc' => route('jobs.category', ['category' => $cat->slug]),
                        'lastmod' => $cat->updated_at->toISOString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ]);
                }
            });

        // Add unique job locations
        $locations = Opening::active()
            ->select('location', DB::raw('MAX(updated_at) as updated_at'))
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->get();

        foreach ($locations as $location) {
            $locationSlug = strtolower(str_replace(' ', '-', $location->location));
            $urls->push([
                'loc' => route('jobs.location', ['location' => $locationSlug]),
                'lastmod' => $location->updated_at->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);
        }

        // Add location + category combinations
        JobCategory::active()->select(['slug', 'updated_at'])
            ->chunk(100, function ($cats) use ($urls, $locations) {
                foreach ($cats as $cat) {
                    foreach ($locations as $location) {
                        $locationSlug = strtolower(str_replace(' ', '-', $location->location));
                        $urls->push([
                            'loc' => route('jobs.location.category', ['location' => $locationSlug, 'category_slug' => $cat->slug]),
                            'lastmod' => max($cat->updated_at, $location->updated_at)->toISOString(),
                            'changefreq' => 'weekly',
                            'priority' => '0.7',
                        ]);
                    }
                }
            });

        // Add openings
       Opening::active()->select(['slug', 'updated_at'])
            ->chunk(100, function ($jobs) use ($urls) {
                foreach ($jobs as $job) {
                    $urls->push([
                        'loc' => route('jobs.show', $job->slug),
                        'lastmod' => $job->updated_at->toISOString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ]);
                }
            });

        // Add blog posts
        Post::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->chunk(100, function ($posts) use ($urls) {
                foreach ($posts as $post) {
                    $urls->push([
                        'loc' => route('blog.show', $post->slug),
                        'lastmod' => $post->updated_at->toISOString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ]);
                }
            });

        //  Add blog categories
        //    Category::whereHas('posts', function ($query) {
        //            $query->published();
        //        })
        //        ->select(['slug', 'updated_at'])
        //        ->chunk(50, function ($categories) use ($urls) {
        //            foreach ($categories as $category) {
        //                $urls->push([
        //                    'loc' => url('/blog?category=' . $category->slug),
        //                    'lastmod' => $category->updated_at->toISOString(),
        //                    'changefreq' => 'weekly',
        //                    'priority' => '0.6',
        //                ]);
        //            }
        //        });

        return $this->buildXml($urls);
    }

    private function buildXml($urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
        $xml .= ' <url>' . PHP_EOL;
            $xml .= ' <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= ' <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= ' <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= ' <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= ' </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function robots(): Response
    {
        $robots = Cache::remember('robots_txt', 86400, function () {
            $generalSettings = app(GeneralSettings::class);
            $allowIndexing = $generalSettings->search_engine_indexing ?? false;

            $robots = '';

            if ($allowIndexing) {
                $robots .= "User-agent: *\n";
                $robots .= "Allow: /\n";
                $robots .= "Disallow: /admin/\n";
                $robots .= "Disallow: /livewire/\n";
                $robots .= "Disallow: /storage/livewire-tmp/\n";
                $robots .= "\n";
                $robots .= "Sitemap: " . route('sitemap') . "\n";
            } else {
                $robots .= "User-agent: *\n";
                $robots .= "Disallow: /\n";
            }

            return $robots;
        });

        return response($robots, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400', // Cache for 24 hours
        ]);
    }
}
