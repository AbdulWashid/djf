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
        if ($this->location && $this->category) {
            $type = Faq::TYPE_BOTH;
        } elseif ($this->location) {
            $type = Faq::TYPE_LOCATION;
        } elseif ($this->category) {
            $type = Faq::TYPE_CATEGORY;
        } else {
            $type = Faq::TYPE_DEFAULT;
        }

        $this->faqs = Faq::active()->where('section', $this->section)->where('type', $type)->get();
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
        return json_encode($schema);
    }
}
