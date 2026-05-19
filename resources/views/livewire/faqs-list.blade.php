@php
$rand = rand();
@endphp
<div class="accordion accordion-flush" id="accordionFlushExample-{{$rand}}" >
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
                 aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample-{{$rand}}">
                <div class="accordion-body">
                    <div class="mb-15">
                        {!! $faq->answer !!}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <h5 class="text-center">No FAQs found</h5>

    @endforelse

        @if(isset($faqsSchema))
            @push('js')
                <script type="application/ld+json">
                    {!! $faqsSchema !!}
                </script>
            @endpush
        @endif


</div>


