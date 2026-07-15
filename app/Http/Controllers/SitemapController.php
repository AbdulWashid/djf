<?php

namespace App\Http\Controllers;

use App\Models\Blog\Post;
use App\Models\Blog\Category;
use App\Models\JobCategory;
use App\Models\Opening;
use App\Models\Location;
use App\Settings\GeneralSettings;
use DB;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\StaticPage;
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
            $data = $this->gatherUrlsforhtml();

            return view('sitemap.index', [
                'groups' => $data['groups'],
                'totalUrls' => $data['totalUrls']
            ])->render();
        });

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function gatherUrlsforhtml(): array
    {
        $groups = [
            'Main Pages'      => collect(),
            'Static Pages'    => collect(),
            'Job Categories'  => collect(),
            'Job Locations'   => collect(),
            'Jobs'            => collect(),
            'Blogs'           => collect(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Main Pages
        |--------------------------------------------------------------------------
        */

        $mainPages = [
            route('home'),
            route('jobs'),
            route('blog'),
            route('faqs'),
            route('contact-us'),
            // route('sitemap'),
            // route('sitemap.html'),
            route('job-categories'),
            // route('employer.register'),
            // route('employer.login'),
            // route('employer.password.request'),
            // route('employer.verification.notice'),
            // route('candidate.register'),
            // route('candidate.login'),
            // route('candidate.password.request'),
            // route('candidate.verification.notice'),
        ];

        foreach ($mainPages as $url) {
            $groups['Main Pages']->push([
                'title' => ucfirst(last(explode('/', trim($url, '/')))) ?: 'Home',
                'loc'   => $url,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Static Pages
        |--------------------------------------------------------------------------
        */

        StaticPage::where('status', 1)
            ->select('slug')
            ->chunk(100, function ($pages) use (&$groups) {

                foreach ($pages as $page) {
                    $groups['Static Pages']->push([
                        'title' => Str::headline($page->slug),
                        'loc'   => route('page', $page->slug),
                    ]);
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        JobCategory::active()
            ->select('slug', 'name')
            ->chunk(100, function ($categories) use (&$groups) {

                foreach ($categories as $category) {
                    $groups['Job Categories']->push([
                        'title' => $category->name,
                        'loc'   => route('jobs.category', [
                            'category' => $category->slug
                        ]),
                    ]);
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Locations
        |--------------------------------------------------------------------------
        */

            Location::whereHas('openings', function ($query) {
                    $query->active();
                })
                ->orderBy('name')
                ->get()
                ->each(function ($location) use (&$groups) {
                    $groups['Job Locations']->push([
                        'title' => $location->name,
                        'loc' => route('jobs.location', [
                            'location' => Str::slug($location->name),
                        ]),
                    ]);
                });
        /*
        |--------------------------------------------------------------------------
        | Jobs
        |--------------------------------------------------------------------------
        */

        Opening::active()
            ->select('title', 'slug')
            ->chunk(100, function ($jobs) use (&$groups) {

                foreach ($jobs as $job) {

                    $groups['Jobs']->push([
                        'title' => $job->title,
                        'loc'   => route('jobs.show', $job->slug),
                    ]);
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Blogs
        |--------------------------------------------------------------------------
        */

        Post::published()
            ->select('title', 'slug')
            ->chunk(100, function ($posts) use (&$groups) {

                foreach ($posts as $post) {

                    $groups['Blogs']->push([
                        'title' => $post->title,
                        'loc'   => route('blog.show', $post->slug),
                    ]);
                }
            });

        return [
            'groups' => $groups,
            'totalUrls' => collect($groups)->sum(fn($group) => $group->count())
        ];
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
            // ['url' => route('sitemap.html'), 'priority' => '0.9', 'changefreq' => 'daily'],
            // ['url' => route('sitemap'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('blog'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('faqs'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('contact-us'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => route('job-categories'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            // ['url' => route('employer.register'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            // ['url' => route('employer.login'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            // ['url' => route('employer.password.request'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            // ['url' => route('employer.verification.notice'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            // ['url' => route('candidate.register'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            // ['url' => route('candidate.login'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            // ['url' => route('candidate.password.request'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            // ['url' => route('candidate.verification.notice'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls->push([
                'loc' => $page['url'],
                'lastmod' => now()->toISOString(),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ]);
        }

        $pages = StaticPage::select('slug', 'status', 'updated_at')->where('status', 1)->get();
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

        $locations = Location::withCount([
                'openings as jobs_count' => fn ($query) => $query->active(),
            ])
            ->withMax([
                'openings as updated_at' => fn ($query) => $query->active(),
            ], 'updated_at')
            ->get();

        foreach ($locations as $location) {
            if ($location->jobs_count == 0) {
                continue;
            }

            $urls->push([
                'loc' => route('jobs.location', [
                    'location' => Str::slug($location->name),
                ]),
                'lastmod' => Carbon::parse($location->updated_at)->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);
        }

        $locationCategoryStats = Opening::active()
            ->with('location')
            ->select(
                'location_id',
                'job_category_id',
                DB::raw('MAX(updated_at) as updated_at'),
                DB::raw('COUNT(*) as jobs_count')
            )
            ->whereNotNull('location_id')
            ->whereNotNull('job_category_id')
            ->groupBy('location_id', 'job_category_id')
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
                    'location' => Str::slug($item->location->name),
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
