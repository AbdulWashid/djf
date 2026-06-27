<?php

use Livewire\Volt\Component;
use App\Models\JobListingContent;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    public ?string $category = null;
    public ?string $location = null;

    public string $content = '';
    public string $faqTitle = '';

    public array $faqs = [];

    public string $faqSchema = '';

    public function mount(): void
    {
        $record = JobListingContent::first();
        $record = rememberIfEnabled('job_listing_content', now()->addMinutes(30), fn() => JobListingContent::first());
        if (!$record) {
            return;
        }

        // Decide which content to use
        if ($this->location && $this->category) {
            $data = $record->location_category ?? [];
        } elseif ($this->location) {
            $data = $record->location ?? [];
        } elseif ($this->category) {
            $data = $record->category ?? [];
        } else {
            $data = $record->without_filter ?? [];
        }

        $this->content = $this->replacePlaceholders($data['content'] ?? '');

        $this->faqTitle = $this->replacePlaceholders($data['faq_title'] ?? '');

        $this->faqs = collect($data['faqs'] ?? [])
            ->map(function ($faq) {
                return [
                    'question' => $this->replacePlaceholders($faq['question'] ?? ''),
                    'answer' => $this->replacePlaceholders($faq['answer'] ?? ''),
                ];
            })
            ->values()
            ->toArray();

        $this->faqSchema = $this->generateFaqSchema();
    }

    protected function replacePlaceholders(?string $text): string
    {
        return strtr($text ?? '', [
            '{category-name}' => $this->category ?? '',
            '{place-name}' => $this->location ?? '',
        ]);
    }

    protected function generateFaqSchema(): string
    {
        if (empty($this->faqs)) {
            return '';
        }

        $mainEntity = [];

        foreach ($this->faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => strip_tags($faq['question']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($faq['answer']),
                ],
            ];
        }

        return json_encode(
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $mainEntity,
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }
};

?>

<div>

    @if (!empty($content))
        <section class="section-box mt-60">
            <div class="container">

                <div class="job-listing-content row single-body">

                    {!! $content !!}

                </div>

            </div>
        </section>
    @endif


    @if (count($faqs))

        <section class="section-box mt-50 mb-50">

            <div class="container">

                @if ($faqTitle)
                    <div class="mb-4">

                        <h2>{{ $faqTitle }}</h2>

                    </div>
                @endif

                <x-frontend.faq-grid :items="$faqs" :category="$category" :location="$location" />

            </div>

        </section>

    @endif


    @if ($faqSchema)
        @push('schema')
            <script type="application/ld+json">
                {!! $faqSchema !!}
            </script>
        @endpush
    @endif

</div>
