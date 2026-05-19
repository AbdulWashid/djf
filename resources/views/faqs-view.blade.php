@php
    use App\Models\Faq;
@endphp

<x-frontend.main>
    <section class="mt-80">
        <div class="container">
            <div class="row align-items-end mb-50">
                <div class="col-lg-7">
                    <span class="text-blue wow animate__animated animate__fadeInUp">Questions</span>
                    <h3 class="mt-20 wow animate__animated animate__fadeInUp">Frequently Ask Questions</h3>
                </div>
                <div class="col-lg-2"></div>

            </div>
            <div class="row">

                <div class="col-lg-8">
                    <div class="accordion accordion-flush">
                        @php
                            $faqs = Faq::active()->where('section', 'general')->get();
                        @endphp
                        @forelse($faqs as $faq)
                            <div class="accordion-item">
                                <p class="accordion-header" id="flush-headingOne2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapseOne{{$faq->id}}" aria-expanded="false"
                                            aria-controls="flush-collapseOne{{$faq->id}}">
                                        {{$faq->question}}
                                    </button>
                                </p>
                                <div id="flush-collapseOne{{$faq->id}}" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample2">
                                    <div class="accordion-body">
                                        <div class="mb-15">
                                            {!! $faq->answer !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p>No FAQs found</p>

                        @endforelse


                    </div>
                </div>
            </div>
        </div>
    </section>

</x-frontend.main>
