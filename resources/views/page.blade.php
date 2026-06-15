@php
    $pageType = $page->slug === 'about-us' ? 'about' : 'standard';
@endphp

<x-frontend.main page-type="{{ $pageType }}" page-title="{{ $page->meta_title }}"
    page-description="{{ $page->meta_description }}" page-image="{{ $page->image }}"
    meta-keywords="{{ $page->meta_keywords }}" twitter-tags="{{ $page->twitter_tags }}" og-tags="{{ $page->og_tags }}">

    <style>
        h1.page_title {
            font-size: 3rem;
            line-height: 1;
            margin-bottom: 1rem;
            text-align: center;
            color: #000;
            font-weight: 700;

        }
    </style>

    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li>{{ $page->title }}</li>
            </ul>
        </div>
    </div>

    <section class="mt-20">
        <div class="container">
            <div class="row align-items-end mb-50">
                <div class="col-lg-12 text-center">
                    <h1 class="mt-20 wow animate__animated animate__fadeInUp page_title">{{ $page->title }}</h1>
                </div>
                <div class="col-lg-2"></div>

            </div>
            <div class="row single-body">
                {!! $page->content !!}
            </div>

            @if ($page->faqs)
                <div class="row mt-50">
                    <div class="col-lg-12">
                        <h4 class="heading-border"><span>{{ $page->faq_title ?? 'Frequently asked questions' }}</span>
                        </h4>

                        <div class="accordion accordion-flush">
                            @forelse($page->faqs as $key=> $faq)
                                <div class="accordion-item">
                                    <p class="accordion-header" id="flush-headingOne2">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapseOne{{ $key }}"
                                            aria-expanded="false" aria-controls="flush-collapseOne{{ $key }}">
                                            {{ $faq['question'] }}
                                        </button>
                                    </p>
                                    <div id="flush-collapseOne{{ $key }}" class="accordion-collapse collapse"
                                        aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample2">
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
        </div>
    </section>
</x-frontend.main>
