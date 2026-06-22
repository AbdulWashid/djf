<?php

use Livewire\Volt\Component;
use App\Models\Faq;
use Livewire\Attributes\Layout;

new #[
    Layout('components.frontend.main', [
        'pageType' => 'FAQ\'s',
        'pageTitle' => 'How to Find a Job in Dubai? FAQs & Answers | DubaiJobFinder',
        'pageDescription' => 'Stuck in your job search? Read our comprehensive FAQs on Dubai jobs, interview tips, and recruitment updates to land your dream career in Dubai.',
    ]),
]
class extends Component {
    public $faqs;
    public function mount(): void
    {
        $this->faqs = Faq::active()->where('section', 'general')->get();
    }
}; ?>

<div>
    <section class="mt-80">
        <div class="container">
            <div class="row align-items-end mb-50">
                <div class="col-lg-7">
                    <span class="text-blue wow animate__animated animate__fadeInUp">Questions</span>
                    <h1 class="mt-20 wow animate__animated animate__fadeInUp">Frequently Ask Questions</h1>
                </div>
                <div class="col-lg-2"></div>

            </div>
            <div class="row">

                <div class="col-lg-8">
                    <livewire:pages.components.faqs-list section="general" />
                </div>
            </div>
        </div>
    </section>
</div>
