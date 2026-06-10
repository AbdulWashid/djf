<!-- Content -->
<div>
    @if (isset($schemaData))
        @push('js')
            <script type="application/ld+json">
                {!! $schemaData !!}
            </script>
        @endpush
    @endif

    {{-- Preview Banner --}}
    @if ($isPreview)
        <div
            class="fixed top-0 left-0 right-0 z-50 px-4 py-2 font-semibold text-center text-white bg-orange-500 shadow-lg">
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-eye"></i>
                <span>Preview Mode - This is how your blog post will look when published</span>
                <button onclick="window.close()"
                    class="px-3 py-1 ml-4 transition-colors rounded bg-white/20 hover:bg-white/30">
                    <i class="mr-1 fa-solid fa-times"></i> Close Preview
                </button>
            </div>
        </div>
        <div class="h-12"></div> {{-- Spacer for fixed banner --}}
    @endif


    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>

                @php
                    $cat_items = [
                        ['label' => 'Blog', 'url' => route('blog')],
                        //            ['label' => $post->category->name, 'url' => route('blog', ['category' => $post->category->id])],
                        ['label' => $post->title],
                    ];
                    $pg_title = $post->title;
                @endphp

                @foreach ($cat_items as $item)
                    @if (isset($item['url']))
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @else
                        <li>{{ $item['label'] }}</li>
                    @endif
                @endforeach
                @if (count($cat_items) === 0)
                    <li>{{ $pg_title }}</li>
                @endif
            </ul>
        </div>
    </div>
    <div class="archive-header pt-50 pb-50 text-center">
        <div class="container">
            <h1 class="h1 mb-30 text-center w-75 mx-auto">
                {{ $pg_title }}
            </h1>
            <div class="post-meta text-muted d-flex align-items-center mx-auto justify-content-center">
                <div class="author d-flex align-items-center mr-30">
                    @if ($post->author && $post->author->profile_photo_path)
                        <img src="{{ Storage::url($post->author->profile_photo_path) }}" alt="{{ $post->author->name }}"
                            width="30" height="30" class="rounded-[50%]" />
                    @else
                        <img src="https://placehold.co/45x45?text={{ substr($post->author->name ?? 'A', 0, 1) }}"
                            alt="{{ $post->author->name ?? 'Author' }}" width="30" height="30"
                            class="rounded-[50%]" />
                    @endif
                    <span>{{ $post->author->name ?? 'Anonymous' }}</span>
                </div>
                <div class="date mr-30">
                    <span><i class="fi-rr-edit mr-5 text-grey-6"></i>
                        {{ $post->published_at->format('M d, Y') }}</span>
                </div>
                @if ($post->category)
                    <div>
                        <a href="{{ route('blog', ['category' => $post->category->id]) }}"
                            class="rounded-[50px] bg-color-black/5 px-[26px] py-1.5 text-black/60 hover:bg-color-blue hover:text-white">
                            {{ $post->category->name }}
                        </a>
                    </div>
                @endif
                @if ($post->is_featured)
                    <div>
                        <span class="rounded-[50px] bg-orange-500 px-[26px] py-1.5 text-white">
                            Featured
                        </span>
                    </div>
                @endif

            </div>
        </div>
    </div>
    <div class="post-loop-grid">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="single-body">


                        <figure class="mb-30">

                            <a href="{{ $post->getUrl() }}" wire:click="trackView('{{ $post->id }}')">
                                @if ($post->hasFeaturedImage())
                                    <img src="{{ $post->getFeaturedImageUrl('large') }}" alt="{{ $post->title }}"
                                        width="856" height="540"
                                        class="object-cover w-full h-auto transition-all duration-300 scale-100 group-hover:scale-105" />
                                    {{-- @else 
                                    <img src="https://placehold.co/856x540?text={{ urlencode($post->title) }}" 
                                            alt="{{ $post->title }}"  width="856" height="540" 
                                            class="object-cover w-full h-auto transition-all duration-300 scale-100 group-hover:scale-105"/>  --}}
                                @endif
                            </a>
                        </figure>
                        <div class="excerpt mb-30">
                            <p> {{ $post->content_overview }}</p>
                        </div>

                        <div class="single-content">

                            {!! $post->content_raw !!}


                        </div>

                        {{-- <div class="author-bio p-30 mt-50 border-radius-15 bg-white"> 
                            <div class="author-image mb-15"> 
                                <a href="author.html">
                                    <img src="assets/imgs/avatar/ava_14.png" alt="" class="avatar">
                                </a> 
                                <div class="author-infor"> 
                                    <h5 class="mb-5">Steven Job</h5> 
                                    <p class="mb-0 text-muted font-xs"> 
                                        <span class="mr-10">306 posts</span> 
                                        <span class="has-dot">Since 2012</span> 
                                    </p> 
                                </div> 
                            </div> 
                            <div class="author-des"> 
                                <p>
                                    Hi, I'm a recruiter with over 25 years of experience. I have worked in many 
                                    multinational companies and corporations. With my experiences, I hope my articles 
                                    will bring you knowledge and inspiration.
                                </p> 
                            </div> 
                        </div>  --}}

                        @if ($post->faqs)

                            <div class="row mt-50">
                                <div class="col-lg-12">
                                    <h2 class="heading-border"><span>{{ $post->faq_title }}</span></h2>

                                    <div class="accordion accordion-flush">
                                        @php
                                            $fqlist = [];
                                        @endphp
                                        @forelse($post->faqs as $key=> $faq)
                                            @php
                                                $fqlist[$faq['question']] = strip_tags($faq['answer']);
                                            @endphp

                                            <div class="accordion-item">
                                                <p class="accordion-header" id="flush-headingOne2">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#flush-collapseOne{{ $key }}"
                                                        aria-expanded="false"
                                                        aria-controls="flush-collapseOne{{ $key }}">
                                                        {{ $faq['question'] }}
                                                    </button>
                                                </p>
                                                <div id="flush-collapseOne{{ $key }}"
                                                    class="accordion-collapse collapse"
                                                    aria-labelledby="flush-headingOne"
                                                    data-bs-parent="#accordionFlushExample2">
                                                    <div class="accordion-body">
                                                        <div class="mb-15">
                                                            {!! $faq['answer'] !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <h5 class="text-center">No FAQs found</h5>
                                        @endforelse


                                    </div>
                                </div>
                            </div>
                            @if (isset($faqSchema))
                                @push('js')
                                    <script type="application/ld+json">
                                        {!! $faqSchema !!}
                                    </script>
                                @endpush
                            @endif
                        @endif


                        @if (count($relatedPosts) > 0)
                            <div class="related-posts mt-50">
                                <h4 class="mb-30">Related Posts</h4>
                                <div class="box-swiper">
                                    <div class="swiper-container swiper-group-3">
                                        <div class="swiper-wrapper pb-30 pt-5">
                                            @foreach ($relatedPosts as $related)
                                                <div class="swiper-slide">
                                                    <div class="card-grid-3 hover-up p-15">
                                                        <a href="{{ $related->getUrl() }}">
                                                            @if ($related->hasFeaturedImage())
                                                                <figure><img
                                                                        class="flex items-center justify-center w-full h-48 bg-gray-200"
                                                                        alt="{{ $related->title }}"
                                                                        src="{{ $related->getFeaturedImageUrl('square_thumb') }}" />
                                                                </figure>
                                                            @else
                                                                <div
                                                                    class="flex items-center justify-center w-full h-48 bg-gray-200">
                                                                    <span class="text-gray-400">No Image</span>
                                                                </div>
                                                            @endif
                                                        </a>
                                                        <h6 class="heading-md mt-15 mb-0"><a
                                                                href="{{ $related->getUrl() }}">{{ $related->title }}</a>
                                                        </h6>
                                                    </div>
                                                </div>
                                            @endforeach


                                        </div>
                                        <div class="swiper-pagination swiper-pagination3"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>


                </div>


                <div class="col-lg-4 col-md-12 col-sm-12 col-12 pl-40 pl-lg-15 mt-lg-30">
                    <div class="sidebar-shadow sidebar-news-small">
                        <h5 class="sidebar-title">Latest news</h5>
                        <div class="post-list-small">
                            @foreach ($recentPosts as $recentPost)
                                <div class="post-list-small-item d-flex align-items-center">
                                    <figure class="thumb mr-15">
                                        @if ($recentPost->hasFeaturedImage())
                                            <img src="{{ $recentPost->getFeaturedImageUrl('square_thumb') }}"
                                                alt="{{ $recentPost->title }}" />
                                        @else
                                            <img src="https://placehold.co/150x150?text={{ substr($recentPost->title, 0, 10) }}"
                                                alt="{{ $recentPost->title }}" />
                                        @endif
                                    </figure>
                                    <div class="content">
                                        <h5><a href="{{ $recentPost->getUrl() }}"
                                                wire:click="trackView('{{ $recentPost->id }}')">{{ $recentPost->title }}</a>
                                        </h5>
                                        <div class="post-meta text d-flex align-items-end flex-column">
                                            <div class="author">
                                                <span>{{ $recentPost->author->name ?? 'Anonymous' }}</span>
                                            </div>

                                            <div class="date">
                                                <span>{{ $recentPost->published_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach


                        </div>
                    </div>
                    <div class="sidebar-shadow widget-categories">
                        <h5 class="sidebar-title">Category</h5>
                        <ul>


                            @foreach ($categories as $category)
                                <li class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('blog', ['category' => $category->id]) }}"
                                        class="w-full text-left">
                                        {{ $category->name }}
                                    </a>
                                    <span>{{ $category->posts_count }}</span>


                                </li>
                            @endforeach


                        </ul>
                    </div>

                    <div class="sidebar-shadow">
                        <h5 class="sidebar-title">Popular Tags</h5>
                        <div class="block-tags">

                            @forelse($popularTags as $tag)
                                <button wire:click="searchByTag('{{ is_object($tag) ? $tag->name : $tag['name'] }}')"
                                    class="btn btn-tags-sm mb-10 mr-5">

                                    <span>
                                        @if (is_object($tag) && isset($tag->name) && is_string($tag->name))
                                            {{ $tag->name }}
                                        @elseif(is_object($tag) && isset($tag->name) && is_object($tag->name))
                                            {{ $tag->name->{app()->getLocale()} ?? '' }}
                                        @elseif(is_array($tag) && isset($tag['name']) && is_string($tag['name']))
                                            {{ $tag['name'] }}
                                        @elseif(is_array($tag) && isset($tag['name']) && is_array($tag['name']))
                                            {{ $tag['name'][app()->getLocale()] ?? '' }}
                                        @else
                                            {{ is_string($tag) ? $tag : json_encode($tag) }}
                                        @endif
                                    </span>

                                </button>

                            @empty
                                No tags found
                            @endforelse


                        </div>
                    </div>

                </div>
            </div>


        </div>
    </div>

</div>
<!-- End Content -->
