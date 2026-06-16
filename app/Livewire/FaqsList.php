<?php

namespace App\Livewire;

use App\Models\Faq;
use Livewire\Component;

class FaqsList extends Component
{
    public $section;
    public $category;
    public $location;
    public $faqs;

    public function mount(): void
    {
        $this->faqs = Faq::active()->where('section', $this->section)->get();
    }
    public function render()
    {
        $faqsSchema = $this->generateFaqSchema();
        return view('livewire.faqs-list', compact('faqsSchema'));
    }

    protected function generateFaqSchema()
    {
        $faqs = $this->faqs;

        if (!$faqs) {
            return null;
        }
        $mainEntity = [];
        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => strtr($faq['question'],['{category-name}' => $category ?? '', '{place-name}' => $location ?? '']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strtr(strip_tags($faq['answer']), ['{category-name}' => $category ?? '', '{place-name}' => $location ?? '']),
                ],
            ];
        }
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
        return json_encode($schema);
    }
}
