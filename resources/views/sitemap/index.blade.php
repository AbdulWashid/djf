<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sitemap for {{ config('app.name') }}</title>
    <meta name="description"
        content="Browse the full structure of {{ config('app.name') }}. Find pages and sections easily.">
    <link rel="canonical" href="{{ url()->full() }}" />

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff;
        }

        .header {
            background: #2874f0;
            padding: 15px 0;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: auto;
        }

        .header h1 {
            color: #fff;
            margin-bottom: 15px;
            font-size: 28px;
        }

        .search-wrap {
            display: flex;
        }

        .search-wrap input {
            flex: 1;
            padding: 12px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .search-wrap button {
            width: 120px;
            border: none;
            background: #ffd814;
            font-weight: 600;
            cursor: pointer;
        }

        .sitemap-content {
            padding: 25px 0;
        }

        .category {
            margin-bottom: 30px;
        }

        .category h2 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 400;
        }

        .links {
            line-height: 2;
        }

        .links a {
            display: inline-block;
            margin-right: 8px;
            color: #212121;
            text-decoration: none;
            font-size: 14px;
        }

        .links a::after {
            content: "|";
            margin-left: 8px;
            color: #999;
        }

        .links a:last-child::after {
            display: none;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <h1>{{ config('app.name') }}</h1>

            <div class="search-wrap">
                <input type="text" id="sitemapSearch" placeholder="Search jobs, blogs, pages...">

                <button>SEARCH</button>
            </div>
        </div>
    </div>

    <div class="container sitemap-content">

        @foreach ($groups as $title => $links)
            @if ($links->count())
                <div class="category sitemap-group">

                    <h2>
                        {{ $title }}
                        <small>({{ $links->count() }})</small>
                    </h2>

                    <div class="links">

                        @foreach ($links as $link)
                            <a href="{{ $link['loc'] }}" class="sitemap-link">

                                {{ $link['title'] }}

                            </a>
                        @endforeach

                    </div>

                </div>
            @endif
        @endforeach

    </div>

    <script>
        function debounce(func, delay = 300) {
            let timer;

            return function(...args) {
                clearTimeout(timer);

                timer = setTimeout(() => {
                    func.apply(this, args);
                }, delay);
            };
        }

        const sitemapSearch = document.getElementById('sitemapSearch');

        sitemapSearch.addEventListener('input', debounce(function() {

            const value = this.value.toLowerCase().trim();

            document.querySelectorAll('.sitemap-group').forEach(group => {

                let found = false;

                group.querySelectorAll('.sitemap-link').forEach(link => {

                    const match = link.textContent
                        .toLowerCase()
                        .includes(value);

                    link.style.display = match ? 'inline-block' : 'none';

                    if (match) {
                        found = true;
                    }
                });

                const titleMatch = group.querySelector('h2')
                    .textContent
                    .toLowerCase()
                    .includes(value);

                group.style.display = (found || titleMatch) ? '' : 'none';
            });

        }, 300));
    </script>
</body>

</html>
