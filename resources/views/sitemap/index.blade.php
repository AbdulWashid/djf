<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sitemap for {{ config('app.name') }}</title>
    <meta name="description"
        content="Browse the full structure of {{ config('app.name') }}. Find pages and sections easily.">
    <style>
        :root {
            --bg: #f5f7fb;
            --panel: #ffffff;
            --border: #d8e0ea;
            --text: #17212b;
            --muted: #5e6b78;
            --accent: #0f6fff;
            --accent-soft: #eaf2ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .sitemap-header {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
            padding: 28px 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }

        .sitemap-header .wrap,
        .sitemap-main,
        .footerinfo {
            max-width: 1180px;
            margin: 0 auto;
        }

        .sitemap-header h1 {
            margin: 0;
            font-size: clamp(26px, 3vw, 40px);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .subtitle {
            margin-top: 8px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
        }

        .sitemap-main {
            padding: 28px 20px 42px;
        }

        .sitemap-toolbar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .sitemap-toolbar input {
            width: 100%;
            max-width: 420px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 14px;
            font-size: 15px;
            background: var(--panel);
            outline: none;
        }

        .sitemap-toolbar input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(15, 111, 255, 0.12);
        }

        .map-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .map-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .map-card h2 {
            margin: 0;
            padding: 18px 20px;
            font-size: 18px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(180deg, #fff, #f9fbff);
        }

        .nav-tree {
            padding: 18px 20px 24px;
        }

        .nav-tree ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .nav-tree li {
            margin: 0;
            padding: 0;
        }

        .nav-tree .group {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px dashed var(--border);
        }

        .nav-tree .group:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: 0;
        }

        .nav-tree .group-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 15px;
            color: var(--text);
            background: var(--accent-soft);
            padding: 8px 12px;
            border-radius: 999px;
        }

        .nav-tree a {
            display: block;
            padding: 9px 12px;
            margin: 6px 0;
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
            word-break: break-word;
        }

        .nav-tree a:hover {
            background: var(--accent-soft);
            color: var(--accent);
            transform: translateX(2px);
        }

        .nav-tree .meta {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: var(--muted);
        }

        .footerinfo {
            padding: 0 20px 28px;
            color: var(--muted);
            font-size: 14px;
        }

        .footerinfo a {
            color: var(--accent);
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .map-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <header class="sitemap-header">
        <div class="wrap">
            <h1>{{ config('app.name') }} Sitemap</h1>
            <div class="subtitle">Browse all important pages, job links, and blog posts in one place.</div>
        </div>
    </header>

    <main class="sitemap-main">
        <div class="sitemap-toolbar">
            <input type="search" id="sitemapSearch" placeholder="Search sitemap pages...">
        </div>

        @php
            $groups = [
                'Main Pages' => $urls->filter(
                    fn($url) => in_array($url['loc'], [
                        route('home'),
                        route('jobs'),
                        route('blog'),
                        route('contact-us'),
                    ]),
                ),
                'Static Pages' => $urls->filter(
                    fn($url) => str_contains($url['loc'], url('/')) && str_contains($url['loc'], '/'),
                ),
                'Jobs' => $urls->filter(
                    fn($url) => str_contains($url['loc'], route('jobs')) ||
                        str_contains($url['loc'], '/job/') ||
                        str_contains($url['loc'], '/jobs/'),
                ),
                'Blog' => $urls->filter(
                    fn($url) => str_contains($url['loc'], route('blog')) || str_contains($url['loc'], '/blog/'),
                ),
            ];
        @endphp

        <div class="map-grid">
            @foreach ($groups as $title => $groupUrls)
                <section class="map-card sitemap-group">
                    <h2>{{ $title }}</h2>
                    <div class="nav-tree">
                        @if ($groupUrls->isEmpty())
                            <p>No items found.</p>
                        @else
                            <ul>
                                @foreach ($groupUrls as $url)
                                    <li class="sitemap-item">
                                        <a href="{{ $url['loc'] }}" target="_top">
                                            {{ $url['loc'] }}
                                            <span class="meta">Last updated:
                                                {{ \Illuminate\Support\Carbon::parse($url['lastmod'])->format('M d, Y') }}
                                                | {{ strtoupper($url['changefreq']) }} | Priority
                                                {{ $url['priority'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </main>

    <footer class="footerinfo">
        Created with <a href="{{ route('sitemap') }}">XML sitemap</a>
    </footer>

    <script>
        (function() {
            const input = document.getElementById('sitemapSearch');
            const items = document.querySelectorAll('.sitemap-item');

            if (!input) {
                return;
            }

            input.addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();

                items.forEach(function(item) {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(term) ? '' : 'none';
                });
            });
        })();
    </script>
</body>

</html>
