<?php

use Livewire\Volt\Component;
use App\Models\Faq;

new class extends Component {
    public $section;
    public $category;
    public $location;
    public $faqs;
    public string $faqsSchema = '';

    // Infinite-scroll state (used only for 'general')
    public int $perPage = 30;
    public int $page = 1;
    public bool $hasMorePages = false;
    public bool $isLoadingMore = false;

    public function mount(): void
    {
        $query = Faq::active()->where('section', $this->section);

        if ($this->section === 'homepage') {
            $this->faqs = $query->take(10)->get();
        } elseif ($this->section === 'general') {
            $paginated = $query->paginate($this->perPage, ['*'], 'page', $this->page);
            $this->faqs = collect($paginated->items());
            $this->hasMorePages = $paginated->hasMorePages();
        } else {
            $this->faqs = $query->get();
        }

        $this->faqsSchema = $this->generateFaqSchema();
    }

    public function loadMore(): void
    {
        if (!$this->hasMorePages || $this->isLoadingMore) {
            return;
        }

        $this->isLoadingMore = true;
        $this->page++;

        $paginated = Faq::active()
            ->where('section', $this->section)
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $this->faqs = $this->faqs->concat($paginated->items());
        $this->hasMorePages = $paginated->hasMorePages();
        $this->isLoadingMore = false;
    }

    protected function generateFaqSchema()
    {
        if (!$this->faqs || $this->faqs->isEmpty()) {
            return null;
        }

        $mainEntity = [];
        foreach ($this->faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => strtr($faq['question'], ['{category-name}' => $this->category ?? '', '{place-name}' => $this->location ?? '']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strtr(strip_tags($faq['answer']), ['{category-name}' => $this->category ?? '', '{place-name}' => $this->location ?? '']),
                ],
            ];
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ]);
    }
}; ?>

<div
    @if ($section === 'general') x-data
        x-init="
            let sentinel = $refs.faqSentinel;
            if (sentinel) {
                let observer = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        $wire.loadMore();
                    }
                }, { rootMargin: '200px' });
                observer.observe(sentinel);
            }
        " @endif>
    <x-frontend.faq-grid :items="$faqs" :category="$category" :location="$location" :columns-of="$section === 'homepage' ? 5 : null" />

    @if ($section === 'general' && $hasMorePages)
        <div wire:key="faq-sentinel" x-ref="faqSentinel" class="w-100 text-center py-4">
            <span wire:loading wire:target="loadMore">Loading more FAQs...</span>
        </div>
    @endif

    @if (isset($faqsSchema) && $faqsSchema)
        @push('schema')
            <script type="application/ld+json">
                {!! $faqsSchema !!}
            </script>
        @endpush
    @endif
</div>
