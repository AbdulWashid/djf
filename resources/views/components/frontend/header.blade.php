@php
    use Datlechin\FilamentMenuBuilder\Models\Menu;

    $brandLogo = $generalSettings->brand_logo ?? null;
    //    dump($siteSettings);
    $brandName = $generalSettings->brand_name ?? ($siteSettings->name ?? config('app.name', 'Dubai Job Finder'));
    $favicon = $generalSettings->site_favicon;

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
            {{--                <div class="header-right"> --}}
            {{--                    <div class="block-signin"> --}}
            {{--                        <a href="#" class="text-link-bd-btom hover-up">Apply Now</a> --}}
            {{--                        <a href="#" class="btn btn-default btn-shadow ml-40 hover-up">Sign in</a> --}}
            {{--                    </div> --}}
            {{--                </div> --}}
        </div>
    </div>
</header>

<div class="mobile-header-active mobile-header-wrapper-style perfect-scrollbar">
    <div class="mobile-header-wrapper-inner">
        <div class="mobile-header-top">
            <div class="user-account">
                <img alt="Dubai Job Finder" src="{{ Storage::url($favicon) }}" />
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
                        </ul>
                    </nav>
                    <!-- mobile menu end -->
                </div>

                <div class="mobile-social-icon mb-50">
                    <h6 class="mb-25">Follow Us</h6>
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
                            <a href="{{ $url }}" target="_blank"><img rel="nofollow noopener noreferrer"
                                    src="{{ asset('assets/imgs/theme/icons/icon-facebook.svg') }}"
                                    alt="Dubai Job Finder" /></a>
                        @elseif($platform == 'twitter' && !empty($url))
                            <a href="{{ $url }}" target="_blank"><img rel="nofollow noopener noreferrer"
                                    src="{{ asset('assets/imgs/theme/icons/icon-twitter.svg') }}"
                                    alt="Dubai Job Finder" /></a>
                        @elseif($platform == 'instagram' && !empty($url))
                            <a href="{{ $url }}" target="_blank"><img rel="nofollow noopener noreferrer"
                                    src="{{ asset('assets/imgs/theme/icons/icon-instagram.svg') }}"
                                    alt="Dubai Job Finder" /></a>
                        @elseif($platform == 'tiktok' && !empty($url))
                            <a href="{{ $url }}" target="_blank"><img rel="nofollow noopener noreferrer"
                                    src="{{ asset('assets/imgs/theme/icons/icon-tiktok.svg') }}"
                                    alt="Dubai Job Finder" /></a>
                        @elseif($platform == 'youtube' && !empty($url))
                            <a href="{{ $url }}" target="_blank"><img rel="nofollow noopener noreferrer"
                                    src="{{ asset('assets/imgs/theme/icons/icon-youtube.svg') }}"
                                    alt="Dubai Job Finder" /></a>
                        @elseif($platform == 'linkedin' && !empty($url))
                            <a href="{{ $url }}" target="_blank"><img rel="nofollow noopener noreferrer"
                                    src="{{ asset('assets/imgs/theme/icons/icon-linkedin.svg') }}"
                                    alt="Dubai Job Finder" /></a>
                        @endif

                        {{--                            @if ($platform == 'tiktok') continue; --}}

                        {{--                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" --}}
                        {{--                               class="icon-socials icon-{{ $platform }}" --}}
                        {{--                               aria-label="{{ $platform }}"> --}}
                        {{--                                <i class="{{ $faIcons[$platform] ?? 'fa-brands fa-'.$platform }}"></i> --}}
                        {{--                            </a> --}}
                        {{--                        @endif --}}
                    @endforeach



                </div>
                <div class="site-copyright">Copyright {{ date('Y') }} &copy; Dubai Job Finder.</div>
            </div>
        </div>
    </div>
</div>
