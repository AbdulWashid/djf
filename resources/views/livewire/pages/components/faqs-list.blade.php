<?php

use Livewire\Volt\Component;
use App\Models\Faq;

new class extends Component {
    public $section;
    public $category;
    public $location;
    public $faqs;
    public string $faqsSchema = '';

    public function mount(): void
    {
        $query = Faq::active()->where('section', $this->section);

        if ($this->section === 'homepage') {
            $query->take(10);
        }

        $this->faqs = $query->get();
        $this->faqsSchema = $this->generateFaqSchema();
    }

    protected function generateFaqSchema()
    {
        if (!$this->faqs) {
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

<div>
    <x-frontend.faq-grid
        :items="$faqs"
        :category="$category"
        :location="$location"
        :columns-of="$section === 'homepage' ? 5 : null"
    />

    @if (isset($faqsSchema) && $faqsSchema)
        @push('schema')
            <script type="application/ld+json">
                {!! $faqsSchema !!}
            </script>
        @endpush
    @endif
</div>
