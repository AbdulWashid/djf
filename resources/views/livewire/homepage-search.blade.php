<div>
    <div class="form-find mt-60 wow animate__animated animate__fadeInUp" data-wow-delay=".2s" style="width: 100%;">
        <form id="homepage-search-form" action="{{ route('jobs') }}" method="GET">
            <input type="text" class="form-input input-keysearch mr-10" placeholder="Job title, Company... "
                name="q" />

            <div wire:ignore class="mr-2">
                <select class="form-input mr-10 select-active" id="location-select" name="location">
                    <option value="">Location</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-default btn-find">Find now</button>
        </form>
    </div>
    <div class="list-tags-banner mt-60 wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
        <strong>Popular Searches:</strong>
        <a href="{{ route('jobs', ['q' => 'designer']) }}">Designer</a>,
        <a href="{{ route('jobs', ['q' => 'developer']) }}">Developer</a>,
        <a href="{{ route('jobs', ['q' => 'web']) }}">Web</a>,
        <a href="{{ route('jobs', ['q' => 'engineer']) }}">Engineer</a>,
        <a href="{{ route('jobs', ['q' => 'senior']) }}">Senior</a>,
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const homepageSearchForm = document.getElementById('homepage-search-form');

            function initSelect2() {
                $('#location-select').select2({
                    placeholder: 'Select Location',
                    allowClear: true,
                    width: '100%'
                });

            }

            initSelect2();

            if (homepageSearchForm) {
                homepageSearchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const location = $('#location-select').val();
                    const q = homepageSearchForm.querySelector('input[name="q"]').value.trim()
                .toLowerCase();
                    let targetUrl = @json(route('jobs.location', '__LOCATION__')).replace('__LOCATION__', encodeURIComponent(
                        String(location).trim().toLowerCase().replace(/\s+/g, '-').replace(
                            /[^a-z0-9-]/g, '')));

                    if (q) {
                        targetUrl += (targetUrl.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(q);
                    }

                    window.location.assign(targetUrl);
                    return false;
                });
            }

            Livewire.on('reinit-select2', () => {
                initSelect2();
            });
        });
    </script>
@endpush
