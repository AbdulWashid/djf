<?php

use Livewire\Volt\Component;
use App\Models\StaticPage;
use Livewire\Attributes\Layout;

new #[Layout('components.frontend.main')] class extends Component {
    public $page;
    public $faqSchema = '';

    public function mount($slug): void
    {
        $this->page = StaticPage::where('slug', $slug)->where('status', 1)->first();
        if ($this->page) {
            if ($this->page->faqs) {
                $mainEntity = [];
                foreach ($this->page->faqs as $faq) {
                    $mainEntity[] = [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags($faq['answer']),
                        ],
                    ];
                }
                $schema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $mainEntity,
                ];
                $this->faqSchema = json_encode($schema);
            }
        } else {
            abort(404);
        }

        view()->share('pageType', $this->page->slug === 'about-us' ? 'about' : 'standard');
        view()->share('pageTitle', $this->page->meta_title);
        view()->share('pageDescription', $this->page->meta_description);
        view()->share('pageImage', $this->page->image);
        view()->share('metaKeywords', $this->page->meta_keywords);
        view()->share('twitterTags', $this->page->twitter_tags);
        view()->share('ogTags', $this->page->og_tags);
    }
}; ?>

<div>
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

                        <x-frontend.faq-grid :items="$page->faqs" />
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
</div>
