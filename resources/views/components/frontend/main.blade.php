@php use Datlechin\FilamentMenuBuilder\Models\Menu; @endphp
@props([
    // === Core Page Props ===
    'pageType' => 'standard', // Maps to $page_type in your switch statement
    'pageTitle' => '', // Used in default case and meta tags
    'pageDescription' => '', // Used in description meta tags
    'metaKeywords' => '', // Used in keywords meta tag
    'twitterTags' => '', // Used in keywords meta tag
    'ogTags' => '', // Used in keywords meta tag

    // === Blog Post Props ===
    'postTitle' => '', // For blog_post page type
    'postCategory' => '', // For blog_post page type
    'authorName' => '', // For blog_post and author page types
    'publishDate' => null, // For blog_post page type

    // === Category Props ===
    'categoryName' => '', // For category page type
    'parentCategory' => '', // For category page type

    // === Search Props ===
    'searchTerm' => '', // For search page type
    'resultsCount' => '', // For search page type

    // === Author Props ===
    'postCount' => '', // For author page type

    // === Optional SEO Props ===
    'canonicalUrl' => null, // Override canonical URL
    'ogImage' => null, // Override OG image
    'twitterImage' => null, // Override Twitter image
    'schemaData' => null, // Extra structured data payload(s)
    'breadcrumbItems' => [], // Breadcrumb schema items
    'noIndex' => false, // Add noindex meta tag
])


<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" class="scroll-smooth">

<head>
    @php

        $page_type = $pageType;
        $favicon = $generalSettings->site_favicon;
        $brandLogo = $generalSettings->brand_logo;
        $siteName = $generalSettings->brand_name ?? ($siteSettings->name ?? config('app.name', 'Dubai Job Finder'));

        $separator = $seoSettings->title_separator ?? '|';

        $_main_variables = [
            '{site_name}' => $siteName,
            '{separator}' => $separator,
        ];

        switch ($page_type) {
            case 'blog_post':
                $titleFormat = $seoSettings->blog_title_format ?? '{post_title} ';
                $variables = array_merge($_main_variables, [
                    '{post_title}' => $postTitle ?? '',
                    '{post_category}' => $postCategory ?? '',
                    '{author_name}' => $authorName ?? '',
                    '{publish_date}' => $publishDate ? $publishDate->format('Y') : '',
                ]);
                break;

            case 'category':
                $titleFormat = $seoSettings->category_title_format ?? '{category_name}';
                $variables = array_merge($_main_variables, [
                    '{category_name}' => $categoryName ?? '',
                    '{parent_category}' => $parentCategory ?? '',
                ]);
                break;

            case 'search':
                $titleFormat = $seoSettings->search_title_format ?? 'Search results for "{search_term}"';
                $variables = array_merge($_main_variables, [
                    '{search_term}' => $searchTerm ?? '',
                    '{results_count}' => $resultsCount ?? '',
                ]);
                break;

            case 'author':
                $titleFormat = $seoSettings->author_title_format ?? 'Posts by {author_name}';
                $variables = array_merge($_main_variables, [
                    '{author_name}' => $authorName ?? '',
                    '{post_count}' => $postCount ?? '',
                ]);
                break;

            default:
                $titleFormat = $seoSettings->meta_title_format ?? '{page_title}';
                $variables = array_merge($_main_variables, [
                    '{page_title}' => $pageTitle ?? $title,
                ]);
        }

        // Process the format by replacing placeholders
        $title = str_replace(array_keys($variables), array_values($variables), $titleFormat);

        // Clean up the title (remove double separators, eliminate leading/trailing separators)
        $title = preg_replace(
            '/\s*' . preg_quote($separator) . '\s*' . preg_quote($separator) . '\s*/',
            " $separator ",
            $title,
        );

        $title = trim($title);
        $title = trim($title, " $separator");

        // Fallback if empty
        if (empty(trim($title))) {
            $title = $siteName;
        }

        $breadcrumbItems = is_array($breadcrumbItems) ? $breadcrumbItems : [];

        if (empty($breadcrumbItems)) {
            switch ($page_type) {
                case 'home':
                    $breadcrumbItems = [['label' => $siteName]];
                    break;

                case 'blog_post':
                    $breadcrumbItems = [['label' => 'Blog', 'url' => route('blog')], ['label' => $postTitle ?: $title]];
                    break;

                case 'blog':
                    $breadcrumbItems = [['label' => 'Blog', 'url' => route('blog')]];
                    break;

                case 'job_posting':
                    $breadcrumbItems = [['label' => 'Jobs', 'url' => route('jobs')], ['label' => $pageTitle ?: $title]];
                    break;

                case 'job_listing':
                case 'jobs':
                    $breadcrumbItems = [['label' => 'Jobs', 'url' => route('jobs')]];
                    break;

                case 'job_categories':
                case 'category':
                    $breadcrumbItems = [
                        ['label' => 'Home', 'url' => route('home')],
                        ['label' => $pageTitle ?: 'Categories'],
                    ];
                    break;

                case 'about':
                    $breadcrumbItems = [['label' => 'Home', 'url' => route('home')], ['label' => 'About Us']];
                    break;

                case 'contact':
                    $breadcrumbItems = [['label' => 'Home', 'url' => route('home')], ['label' => 'Contact']];
                    break;

                default:
                    if (!empty($pageTitle)) {
                        $breadcrumbItems = [['label' => 'Home', 'url' => route('home')], ['label' => $pageTitle]];
                    }
                    break;
            }
        }

        $buildBreadcrumbSchema = function (array $items) use ($siteName) {
            $position = 1;
            $listItems = [];

            foreach ($items as $item) {
                if (empty($item['label'])) {
                    continue;
                }

                $entry = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $item['label'],
                ];

                if (!empty($item['url'])) {
                    $entry['item'] = $item['url'];
                }

                $listItems[] = $entry;
            }

            if (empty($listItems)) {
                $listItems[] = [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => $siteName,
                    'item' => url('/'),
                ];
            }

            return [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $listItems,
            ];
        };

        $structuredData = [];

        $organizationSchema = array_filter(
            [
                '@context' => 'https://schema.org',
                '@type' => $seoSettings->schema_type ?: 'Organization',
                'name' => $seoSettings->schema_name ?: $siteName,
                'url' => url('/'),
                'logo' =>
                    $seoSettings->schema_logo ??
                    ($brandLogo ? Storage::url($brandLogo) : asset('superduper/img/favicon.png')),
                'description' =>
                    $seoSettings->schema_description ??
                    ($siteSettings->description ??
                        'Dubai Job Finder provides everything you need to jumpstart your web project with pre-built components, layouts, and tools that enhance development efficiency and productivity.'),
            ],
            fn($value) => filled($value),
        );

        $structuredData[] = $organizationSchema;

        if (!empty($breadcrumbItems) && is_array($breadcrumbItems)) {
            $structuredData[] = $buildBreadcrumbSchema($breadcrumbItems);
        }

        if (!empty($schemaData)) {
            if (is_array($schemaData) && array_is_list($schemaData)) {
                $structuredData = array_merge($structuredData, $schemaData);
            } else {
                $structuredData[] = $schemaData;
            }
        }
    @endphp


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Canonical URL -->
    {{-- <link rel="canonical" href="{{ $canonicalUrl ?? ($seoSettings->canonical_url ?? url()->current()) }}" /> --}}
    <link rel="canonical" href="{{ url()->full() }}" />

    <!-- SEO Meta Tags -->
    <meta name="keywords" content="{{ $metaKeywords ?? ($seoSettings->meta_keywords ?? '') }}" />
    <meta name="description" content="{!! htmlspecialchars_decode($pageDescription) ??
        ($seoSettings->meta_description ?? ($siteSettings->description ?? '')) !!}">

    <!-- Mobile Optimization Meta Tags  -->
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#512B0F">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Schema.org markup (Google) -->
    <meta itemprop="name" content="{!! htmlspecialchars_decode($title) !!}" />


    <meta itemprop="url" content="{{ url()->current() }}">
    <meta itemprop="description" content="{!! htmlspecialchars_decode($pageDescription) ?? ($seoSettings->meta_description ?? $siteSettings->description) !!}">
    <meta itemprop="thumbnailUrl"
        content="{{ $brandLogo ? Storage::url($brandLogo) : asset('storage/images/logo.png') }}">
    <meta itemprop="image"
        content="{{ $seoSettings->schema_logo ?? ($brandLogo ? Storage::url($brandLogo) : asset('storage/images/logo.png')) }}">

    @if ($twitterTags != null)
        {!! html_entity_decode($twitterTags) !!}
    @else
        <!-- Twitter Card -->
        <meta name="twitter:card" content="{{ $seoSettings->twitter_card_type ?? 'summary' }}">
        <meta name="twitter:site" content="{{ $seoSettings->twitter_site ?? '@dubaijobfinder' }}" />
        <meta name="twitter:creator" content="{{ $seoSettings->twitter_creator ?? '@dubaijobfinder' }}" />
        <meta name="twitter:title" content="{{ $title ?? $seoSettings->twitter_title }}">
        <meta name="twitter:description"
            content="{{ $seoSettings->twitter_description ?? ($pageDescription ?? $seoSettings->meta_description) }}" />
        <meta name="twitter:image"
            content="{{ $twitterImage ?? ($seoSettings->twitter_image ?? ($brandLogo ? Storage::url($brandLogo) : asset('storage/images/logo.png'))) }}">
        <meta name="twitter:url" content="{{ url()->current() }}">
    @endif

    @if ($ogTags != null)
        {!! html_entity_decode($ogTags) !!}
    @else
        <!-- Open Graph (Facebook, LinkedIn) -->
        <meta property="og:site_name" content="{{ $siteName ?? $seoSettings->og_site_name }}" />
        <meta property="og:title" content="{{ $title ?? $seoSettings->og_title }}" />
        <meta property="og:type" content="{{ $seoSettings->og_type ?? 'website' }}" />
        <meta property="og:description"
            content="{{ $seoSettings->og_description ?? ($pageDescription ?? $seoSettings->meta_description) }}" />
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:image"
            content="{{ $ogImage ?? ($seoSettings->og_image ?? ($brandLogo ? Storage::url($brandLogo) : asset('storage/images/logo.png'))) }}" />
        <meta property="og:image:width" content="1500">
        <meta property="og:image:height" content="1500">
        <meta property="og:image:type" content="image/jpeg" />
        <meta property="og:image:alt" content="{{ $siteName }}" />
    @endif

    <!-- Verification codes -->
    @if (!empty($seoSettings->verification_codes))
        @foreach ($seoSettings->verification_codes as $verificationCode)
            {!! $verificationCode !!}
        @endforeach
    @endif

    <!-- Additional meta tags -->
    @if ($seoSettings->head_additional_meta)
        {!! $seoSettings->head_additional_meta !!}
    @endif

    <!-- META YEILD -->
    @yield('components.seo.meta')
    <!-- META YEILD end -->
    <title>{{ $title }}</title>

    <!-- My Title  {{ $pageTitle }}   -->

    <!-- Favicon from settings -->
    <link rel="shortcut icon" href="{{ $favicon ? Storage::url($favicon) : asset('superduper/img/favicon.png') }}"
        type="image/x-icon">

    <!-- Theme CSS via Vite -->
    @vite(['resources/css/app.css'])

    <!-- Icon Font -->

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css?v=1.1') }}" />
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('css')

    @stack('ldap')

    <!-- Custom CSS -->
    @if (isset($scriptSettings->custom_css))
        <style>
            {!! $scriptSettings->custom_css !!}
        </style>
    @endif

    @livewireStyles

    <!-- Header scripts -->
    @if (isset($scriptSettings->header_scripts))
        {!! $scriptSettings->header_scripts !!}
    @endif

    <!--  structured data (JSON-LD) -->
    @if (!empty($structuredData))
        {{-- <script id="structured-data-jsonld" type="application/ld+json">
            {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script> --}}
        @php
            $json = json_encode($structuredData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        @endphp

        <script type="application/ld+json" id="structured-data-jsonld">
                {!! $json !!}
        </script>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            const schemaScript = document.getElementById('structured-data-jsonld');

            Livewire.on('schema-updated', (data) => {
                const payload = data[0] || {};

                if (!schemaScript) {
                    console.log('return ');
                    return;
                }

                const schemas = Array.isArray(payload.schemas) ?
                    payload.schemas :
                    (payload.schema ? [payload.schema] : []);

                schemaScript.textContent = JSON.stringify(schemas);

            });
        });
    </script>
</head>

<body>
    <!-- Body start scripts -->
    @if (isset($scriptSettings->body_start_scripts))
        {!! $scriptSettings->body_start_scripts !!}
    @endif

    @if (isset($siteSettings->is_maintenance) && $siteSettings->is_maintenance)
        <div class="maintenance-mode">
            <div class="container">
                <h1>Site Under Maintenance</h1>
                <p>We're currently performing maintenance. Please check back soon.</p>
            </div>
        </div>
    @else
        <div id="preloader-active">
            <div class="preloader d-flex align-items-center justify-content-center">
                <div class="preloader-inner position-relative">
                    <div class="text-center">
                        <img src="{{ asset('assets/imgs/theme/loading.gif') }}" alt="Dubai Job Finder" />
                    </div>
                </div>
            </div>
        </div>
        <x-frontend.header />

        <!--End header-->
        <!-- Content -->
        <main class="main">
            {{ $slot }}
        </main>
        <!-- End Content -->
        <!-- Footer -->
        <footer class="footer mt-50 pt-50 bg-[#EEE]">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-sm-12 mt-4">
                        <a href="{{ route('home') }}">
                            @php
                                $brandLogo = $generalSettings->brand_logo ?? null;
                                $brandName =
                                    $generalSettings->brand_name ??
                                    ($siteSettings->name ?? config('app.name', 'Dubai Job Finder'));
                                $footerLogo = $siteSettings->footer_logo ?? $brandLogo;
                            @endphp

                            @if ($footerLogo)
                                <img src="{{ Storage::url($footerLogo) }}" alt="{{ $brandName }}" width="220"
                                    height="auto" />
                            @endif
                        </a>
                        <div class="mt-20 mb-20 w-3/4">
                            The #1 portal for UAE careers.
                        </div>
                        <ul class="mt-20">
                            <li><span class="fw-bold">Address:</span> {{ $siteSettings->company_address ?? '' }}</li>
                            <li class="text-nowrap">
                                <span class="fw-bold">Email:</span>
                                @if ($siteSettings->company_email)
                                    @php
                                        [$emailUser, $emailDomain] = explode('@', $siteSettings->company_email, 2);
                                    @endphp
                                    <a href="#" data-email-user="{{ $emailUser }}"
                                        data-email-domain="{{ $emailDomain }}"></a>
                                @endif
                            </li>
                            <li class="text-nowrap">
                                <span class="fw-bold">Contact:</span>
                                @if ($siteSettings->company_phone)
                                    <a href="tel:{{ $siteSettings->company_phone }}">
                                        {{ $siteSettings->company_phone }}
                                    </a>
                                @else
                                    {{ '' }}
                                @endif
                            </li>
                        </ul>
                    </div>
                    <div class="col-xl-2 col-sm-3 col-6 mt-4">
                        <p class="h6">Company</p>

                        @php
                            $footerMenu = Menu::location('footer');
                        @endphp

                        <ul class="menu-footer mt-1">
                            @if ($footerMenu)
                                @foreach ($footerMenu->menuItems as $item)
                                    <li>
                                        <a href="{{ $item->url }}"
                                            @if ($item->target) target="{{ $item->target }}" @endif
                                            @if ($item->target && $item->target->value === '_blank') rel="noopener noreferrer" @endif>
                                            {{ $item->title }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <a href="{{ route('home') }}">Home</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-xl-2 col-sm-3 col-6 mt-4">
                        <p class="h6">Location</p>
                        @php
                            $footerLocations = Menu::location('footer-2');
                        @endphp
                        <ul class="menu-footer mt-1">
                            @if ($footerLocations)
                                @foreach ($footerLocations->menuItems as $item)
                                    <li>
                                        <a href="{{ $item->url }}"
                                            @if ($item->target) target="{{ $item->target }}" @endif>
                                            {{ $item->title }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <div class="col-xl-2 col-sm-3 col-6 mt-4">
                        <p class="h6">Category</p>
                        @php
                            $footerCategories = Menu::location('footer-3');
                        @endphp
                        <ul class="menu-footer mt-1">
                            @if ($footerCategories)
                                @foreach ($footerCategories->menuItems as $item)
                                    <li>
                                        <a href="{{ $item->url }}"
                                            @if ($item->target) target="{{ $item->target }}" @endif>
                                            {{ $item->title }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <div class="col-xl-2 col-sm-3 col-6 mt-4">
                        <p class="h6">Trending Jobs</p>
                        @php
                            $footerTrendingJobs = Menu::location('footer-4');
                        @endphp
                        <ul class="menu-footer mt-1">
                            @if ($footerTrendingJobs)
                                @foreach ($footerTrendingJobs->menuItems as $item)
                                    <li>
                                        <a href="{{ $item->url }}"
                                            @if ($item->target) target="{{ $item->target }}" @endif>
                                            {{ $item->title }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom mt-50">
                    <div class="row">
                        <div class="col-md-6">
                            &copy; Copyright {{ date('Y') }},
                            {{ $siteSettings->copyright_text ?? 'All Rights Reserved' }}
                            {{ $generalSettings->brand_name ?? ($siteSettings->name ?? config('app.name', 'Dubai Job Finder')) }}
                        </div>
                        <div class="col-md-6 text-md-end text-start">
                            <div class="footer-social">
                                @php
                                    $socialLinks = [
                                        'facebook' => $siteSocialSettings->facebook_url ?? null,
                                        'twitter' => $siteSocialSettings->twitter_url ?? null,
                                        'instagram' => $siteSocialSettings->instagram_url ?? null,
                                        'linkedin' => $siteSocialSettings->linkedin_url ?? null,
                                        'youtube' => $siteSocialSettings->youtube_url ?? null,
                                        'tiktok' => $siteSocialSettings->tiktok_url ?? null,
                                    ];

                                    $faIcons = [
                                        'twitter' => 'fa-brands fa-x-twitter',
                                        'facebook' => 'fa-brands fa-facebook-f',
                                        'instagram' => 'fa-brands fa-instagram',
                                        'linkedin' => 'fa-brands fa-linkedin-in',
                                        'youtube' => 'fa-brands fa-square-youtube',
                                        'tiktok' => 'fa-brands fa-tiktok',
                                    ];
                                @endphp

                                @foreach ($socialLinks as $platform => $url)
                                    @if (!empty($url))
                                        <a href="{{ $url }}" target="_blank"
                                            rel="nofollow noopener noreferrer"
                                            class="icon-socials icon-{{ $platform }}"
                                            aria-label="{{ $platform }}">
                                        </a>
                                    @endif
                                @endforeach


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- End Footer -->

        <!-- Floating Contact Button -->
        <a href="{{ route('contact-us') }}" class="floating-contact-btn"
            style="position: fixed;
              bottom: 80px;
              right: 30px;
              width: 40px;
              height: 40px;
              background: #474992 ;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              box-shadow: 0 4px 12px rgba(0,0,0,0.15);
              z-index: 999;
              transition: all 0.3s ease;
              text-decoration: none;"
            onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)';"
            onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
            aria-label="Contact Us">
            <i class="fi-rr-envelope" style="color: white; font-size: 24px;"></i>
        </a>

        <!-- Vendor JS-->
        <script src="{{ asset('assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>
        <script src="{{ asset('assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
        <script src="{{ asset('assets/js/vendor/jquery-migrate-3.3.0.min.js') }}"></script>
        <script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/waypoints.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/wow.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/magnific-popup.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/select2.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/isotope.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/scrollup.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/swiper-bundle.min.js') }}"></script>
        <!-- Template  JS -->
        <script src="{{ asset('assets/js/main.js?v=1.0') }}"></script>



        <!-- Cookie Consent -->
        @if (isset($scriptSettings->cookie_consent_enabled) && $scriptSettings->cookie_consent_enabled)
            <div class="cookie-consent js-cookie-consent" style="display: none;">
                <div class="container">
                    <span class="cookie-consent__message">
                        {!! $scriptSettings->cookie_consent_text ??
                            'We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.' !!}
                        @if (isset($scriptSettings->cookie_consent_policy_url) && $scriptSettings->cookie_consent_policy_url)
                            <a href="{{ $scriptSettings->cookie_consent_policy_url }}">Learn more</a>
                        @endif
                    </span>
                    <button class="cookie-consent__agree">
                        {{ $scriptSettings->cookie_consent_button_text ?? 'Accept' }}
                    </button>
                </div>
            </div>
        @endif
    @endif

    <!-- Vite compiled JS -->
    @vite(['resources/js/app.js'])

    {{-- <!--Vendor js--> --}}
    {{-- <script src="{{ asset('superduper/js/vendors/swiper-bundle.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('superduper/js/vendors/fslightbox.js') }}"></script> --}}
    {{-- <script src="{{ asset('superduper/js/vendors/jos.min.js') }}"></script> --}}

    {{-- <script src="{{ asset('superduper/js/main.js') }}"></script> --}}

    @livewireScripts

    {{-- @php
        $livewireManifestPath = base_path('public/vendor/livewire/manifest.json');
        $livewireAssetVersion = file_exists($livewireManifestPath)
            ? json_decode(file_get_contents($livewireManifestPath), true)['/livewire.js'] ?? null
            : null;
    @endphp

    @livewireScriptConfig
    <script
        src="{{ asset('vendor/livewire/livewire.min.js') }}@if ($livewireAssetVersion) ?id={{ $livewireAssetVersion }} @endif">
    </script> --}}

    <!-- Custom JS -->
    @if (isset($scriptSettings->custom_js))
        <script>
            {!! $scriptSettings->custom_js !!}
        </script>
    @endif

    <script>
        document.querySelectorAll('[data-email-user][data-email-domain]').forEach((element) => {
            const email = `${element.dataset.emailUser}@${element.dataset.emailDomain}`;
            element.href = `mailto:${email}`;
            element.textContent = email;
        });
    </script>

    <!-- Footer scripts -->
    @if (isset($scriptSettings->footer_scripts))
        {!! $scriptSettings->footer_scripts !!}
    @endif

    <!-- Body end scripts -->
    @if (isset($scriptSettings->body_end_scripts))
        {!! $scriptSettings->body_end_scripts !!}
    @endif

    @stack('js')
    {{-- <script src="{{ asset('assets/js/livewire.js') }}"></script> --}}
</body>

</html>
