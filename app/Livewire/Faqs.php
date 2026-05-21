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
        return view('livewire.faqs')->layout('components.frontend.main');
    }
}
