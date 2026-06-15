<?php

namespace App\Livewire;

use App\Models\Faq;
use Livewire\Component;

class Faqs extends Component
{
    public $faqs;
    public function mount(): void
    {
        $this->faqs = Faq::active()->where('section', 'general')->get();
    }
    public function render()
    {
        return view('livewire.faqs')->layout('components.frontend.main', [
            'pageType' => 'FAQ\'s',
            'pageTitle' => 'How to Find a Job in Dubai? FAQs & Answers | DubaiJobFinder',
            'pageDescription' => 'Stuck in your job search? Read our comprehensive FAQs on Dubai jobs, interview tips, and recruitment updates to land your dream career in Dubai.',
        ]);
    }
}
