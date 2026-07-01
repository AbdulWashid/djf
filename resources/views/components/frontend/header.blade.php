@php
    use Datlechin\FilamentMenuBuilder\Models\Menu;

    $brandLogo = $generalSettings->brand_logo ?? null;
    $brandName = $generalSettings->brand_name ?? ($siteSettings->name ?? config('app.name', 'Dubai Job Finder'));
    $favicon = $generalSettings->site_favicon;

    // Resolve Auth State
    $guard = null;
    $user = null;

    if (auth('employer')->check()) {
        $guard = 'employer';
        $user = auth('employer')->user();
    } elseif (auth('candidate')->check()) {
        $guard = 'candidate';
        $user = auth('candidate')->user();
    }

    $userName = $guard === 'employer' ? $user->name ?? '' : $user->first_name ?? '';

@endphp


<header class="header sticky-bar">
    <div class="container">
        <div class="main-header">
            <div class="header-left">
                <div class="header-logo">
                    <a href="{{ route('home') }}" class="d-flex"><img alt="Dubai Job Finder"
                            src="{{ Storage::url($brandLogo) }}" style="max-width: 150px;" /></a>
                </div>
                <div class="header-nav">
                    <nav class="nav-main-menu d-none d-xl-block">
                        @php
                            $menu = Menu::location('header');
                        @endphp
                        <ul class="main-menu">
                            @if ($menu)
                                @foreach ($menu->menuItems as $index => $item)
                                    @php
                                        $hasChildren = count($item->children) > 0;
                                        $menuId = 'submenu-' . ($index + 1);
                                    @endphp

                                    <li class="{{ $hasChildren ? 'has-children' : '' }}">
                                        <a href="{{ $item->url }}"
                                            @if ($item->target) target="{{ $item->target }}" @endif>
                                            <span>{{ $item->title }}</span>

                                        </a>

                                        @if ($hasChildren)
                                            <ul class="sub-menu" id="{{ $menuId }}">
                                                @foreach ($item->children as $childIndex => $childItem)
                                                    @php

                                                        $submenuId = $menuId . '-' . ($childIndex + 1);
                                                    @endphp

                                                    <li>
                                                        <a href="{{ $childItem->url }}"
                                                            @if ($childItem->target) target="{{ $childItem->target }}" @endif>
                                                            <span>{{ $childItem->title }}</span>

                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </nav>
                    <div class="burger-icon burger-icon-white d-xl-none">
                        <span class="burger-icon-top"></span>
                        <span class="burger-icon-mid"></span>
                        <span class="burger-icon-bottom"></span>
                    </div>
                </div>
            </div>
            @php
                $user = null;
                $guard = null;
                $name = null;

                if (auth('employer')->check()) {
                    $user = auth('employer')->user();
                    $guard = 'employer';
                    $name = $user->name;
                } elseif (auth('candidate')->check()) {
                    $user = auth('candidate')->user();
                    $guard = 'candidate';
                    $name = $user->first_name;
                }
            @endphp

            <div class="header-right d-none d-xl-flex align-items-center">
                @if (!$user)
                    <div class="d-flex align-items-center gap-3">
                        <div class="dropdown">
                            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">Employer</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="{{ route('employer.login') }}" class="dropdown-item">Login</a></li>
                                <li><a href="{{ route('employer.register') }}" class="dropdown-item">Register</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">Candidate</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="{{ route('candidate.login') }}" class="dropdown-item">Login</a></li>
                                <li><a href="{{ route('candidate.register') }}" class="dropdown-item">Register</a></li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="dropdown">
                        <button class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2" type="button"
                            data-bs-toggle="dropdown">

                            @if (!empty($user->logo))
                                <img src="{{ Storage::url($user->logo) }}" alt="{{ $userName }}" width="45"
                                    height="45" class="rounded-circle border border-2 border-primary shadow-sm p-1"
                                    style="object-fit: cover;">
                            @else
                                <img src="https://placehold.co/45x45?text={{ strtoupper(substr($userName, 0, 1)) }}"
                                    alt="{{ $userName }}" width="45" height="45"
                                    class="rounded-circle border border-2 border-primary shadow-sm p-1">
                            @endif

                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a href="{{ route($guard . '.profile') }}" class="dropdown-item">
                                    {{ $userName }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf

                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>

<div class="mobile-header-active mobile-header-wrapper-style perfect-scrollbar">
    <div class="mobile-header-wrapper-inner">
        <div class="mobile-header-top">
            <div class="user-account">
                <img loading="lazy" alt="Dubai Job Finder" src="{{ Storage::url($favicon) }}" />
            </div>
            <div class="burger-icon burger-icon-white">
                <span class="burger-icon-top"></span>
                <span class="burger-icon-mid"></span>
                <span class="burger-icon-bottom"></span>
            </div>
        </div>
        <div class="mobile-header-content-area">
            <div class="perfect-scroll">

                <div class="mobile-menu-wrap mobile-header-border">
                    <!-- mobile menu start -->
                    <nav>
                        <ul class="mobile-menu font-heading">

                            @if ($menu)
                                @foreach ($menu->menuItems as $index => $item)
                                    @php
                                        $hasChildren = count($item->children) > 0;
                                        $menuId = 'submenu-' . ($index + 1);
                                    @endphp

                                    <li class="{{ $hasChildren ? 'has-children' : '' }}">
                                        <a href="{{ $item->url }}"
                                            @if ($item->target) target="{{ $item->target }}" @endif>
                                            <span>{{ $item->title }}</span>

                                        </a>

                                        @if ($hasChildren)
                                            <ul class="sub-menu" id="{{ $menuId }}">
                                                @foreach ($item->children as $childIndex => $childItem)
                                                    @php
                                                        $submenuId = $menuId . '-' . ($childIndex + 1);
                                                    @endphp

                                                    <li>
                                                        <a href="{{ $childItem->url }}"
                                                            @if ($childItem->target) target="{{ $childItem->target }}" @endif>
                                                            <span>{{ $childItem->title }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            @endif
                            <li class="has-children">
                                <a href="#">
                                    <span>Employer</span>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{ route('employer.register') }}">
                                            <span>Register</span>
                                        </a>
                                    </li>
                                </ul>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{ route('employer.login') }}">
                                            <span>Login</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-children">
                                <a href="#">
                                    <span>Candidate</span>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{ route('candidate.register') }}">
                                            <span>Register</span>
                                        </a>
                                    </li>
                                </ul>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="{{ route('candidate.login') }}">
                                            <span>Login</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </nav>
                    <!-- mobile menu end -->
                </div>
                <div class="mobile-social-icon mb-50">
                    <p class="mb-25">Follow Us</p>
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
                            'youtube' => 'fa-brands fa-youtube',
                            'tiktok' => 'fa-brands fa-tiktok',
                        ];
                    @endphp

                    @foreach ($socialLinks as $platform => $url)
                        @if ($platform == 'facebook' && !empty($url))
                            <a href="{{ $url }}" target="_blank" rel="nofollow noopener noreferrer">
                                <img loading="lazy" src="{{ asset('assets/imgs/theme/icons/icon-facebook.svg') }}"
                                    alt="Dubai Job Finder" />
                            </a>
                        @elseif($platform == 'twitter' && !empty($url))
                            <a href="{{ $url }}" target="_blank" rel="nofollow noopener noreferrer">
                                <img loading="lazy" src="{{ asset('assets/imgs/theme/icons/icon-twitter.svg') }}"
                                    alt="Dubai Job Finder" />
                            </a>
                        @elseif($platform == 'instagram' && !empty($url))
                            <a href="{{ $url }}" target="_blank" rel="nofollow noopener noreferrer">
                                <img loading="lazy" src="{{ asset('assets/imgs/theme/icons/icon-instagram.svg') }}"
                                    alt="Dubai Job Finder" />
                            </a>
                        @elseif($platform == 'tiktok' && !empty($url))
                            <a href="{{ $url }}" target="_blank" rel="nofollow noopener noreferrer">
                                <img loading="lazy" src="{{ asset('assets/imgs/theme/icons/icon-tiktok.svg') }}"
                                    alt="Dubai Job Finder" />
                            </a>
                        @elseif($platform == 'youtube' && !empty($url))
                            <a href="{{ $url }}" target="_blank" rel="nofollow noopener noreferrer">
                                <img loading="lazy" src="{{ asset('assets/imgs/theme/icons/icon-youtube.svg') }}"
                                    alt="Dubai Job Finder" />
                            </a>
                        @elseif($platform == 'linkedin' && !empty($url))
                            <a href="{{ $url }}" target="_blank" rel="nofollow noopener noreferrer">
                                <img loading="lazy" src="{{ asset('assets/imgs/theme/icons/icon-linkedin.svg') }}"
                                    alt="Dubai Job Finder" />
                            </a>
                        @endif
                    @endforeach
                </div>
                <div class="site-copyright">Copyright {{ date('Y') }} &copy; Dubai Job Finder.</div>
            </div>
        </div>
    </div>
</div>
