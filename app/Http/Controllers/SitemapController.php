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
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = rememberIfEnabled('sitemap_xml', now()->addMinutes(30), fn()=>$this->generateSitemap());

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function html(): Response
    {
        $html = rememberIfEnabled('sitemap_html', now()->addMinutes(30), function () {
            $urls = $this->gatherUrls();

            return view('sitemap.index', [
                'urls' => $urls
            ])->render();
        });

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Build collection of sitemap URLs for reuse (XML and HTML views).
     *
     * @return \Illuminate\Support\Collection
     */
    private function gatherUrls(): \Illuminate\Support\Collection
    {
        $urls = collect();

        // Add static pages
        $staticPages = [
            ['url' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => route('jobs'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => route('blog'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('faqs'), 'priority' => '0.9', 'changefreq' => 'daily'],
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

        $pages = \App\Models\StaticPage::select('slug', 'status', 'updated_at')->where('status', 1)->get();
        foreach ($pages as $page) {
            $urls->push([
                'loc' => route('page', $page->slug),
                'lastmod' => $page->updated_at->toISOString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ]);
        }

        $activeCategories = JobCategory::active()->select(['id', 'slug', 'updated_at'])->get()->keyBy('id');

        $categoriesWithJobs = Opening::active()
            ->select('job_category_id', DB::raw('MAX(updated_at) as updated_at'), DB::raw('COUNT(*) as jobs_count'))
            ->whereNotNull('job_category_id')
            ->groupBy('job_category_id')
            ->get();

        foreach ($categoriesWithJobs as $categoryStats) {
            $category = $activeCategories->get($categoryStats->job_category_id);

            if (!$category || (int) $categoryStats->jobs_count === 0) {
                continue;
            }

            $urls->push([
                'loc' => route('jobs.category', ['category' => $category->slug]),
                'lastmod' => max($category->updated_at, $categoryStats->updated_at)->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);
        }

        $locations = Opening::active()
            ->select('location', DB::raw('MAX(updated_at) as updated_at'), DB::raw('COUNT(*) as jobs_count'))
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->get();

        foreach ($locations as $location) {
            if ((int) $location->jobs_count === 0) {
                continue;
            }

            $locationSlug = Str::slug($location->location);
            $urls->push([
                'loc' => route('jobs.location', ['location' => $locationSlug]),
                'lastmod' => $location->updated_at->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);
        }

        $locationCategoryStats = Opening::active()
            ->select(
                'location',
                'job_category_id',
                DB::raw('MAX(updated_at) as updated_at'),
                DB::raw('COUNT(*) as jobs_count')
            )
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->whereNotNull('job_category_id')
            ->groupBy('location', 'job_category_id')
            ->get();

        foreach ($locationCategoryStats as $item) {
            if ((int) $item->jobs_count === 0) {
                continue;
            }

            $category = $activeCategories->get($item->job_category_id);
            if (!$category) {
                continue;
            }

            $urls->push([
                'loc' => route('jobs.location.category', [
                    'location' => Str::slug($item->location),
                    'category_slug' => $category->slug,
                ]),
                'lastmod' => max($category->updated_at, $item->updated_at)->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);
        }

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

        return $urls;
    }

    private function generateSitemap(): string
    {
        return $this->buildXml($this->gatherUrls());
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
        $robots = rememberIfEnabled('robots_txt', now()->addMinutes(30), function () {
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
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
