<div>
    <div class="form-find mt-60 wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
        <form wire:submit.prevent="submit">
            <input type="text"
                   class="form-input input-keysearch mr-10"
                   placeholder="Job title, Company... "
                   name="q"
                   wire:model.defer="q" />

            <div wire:ignore class="mr-2">
                <select class="form-input mr-10 select-active" id="location-select"  wire:model.defer="location">
                    <option value="">Location</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
            </div>


            <button type="submit" class="btn btn-default btn-find">Find now</button>
        </form>
    </div>
    <div class="list-tags-banner mt-60 wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
        <strong>Popular Searches:</strong>
        <a href="{{ route('jobs', ['q' => 'Designer']) }}">Designer</a>,
        <a href="{{ route('jobs', ['q' => 'Developer']) }}">Developer</a>,
        <a href="{{ route('jobs', ['q' => 'Web']) }}">Web</a>,
        <a href="{{ route('jobs', ['q' => 'Engineer']) }}">Engineer</a>,
        <a href="{{ route('jobs', ['q' => 'Senior']) }}">Senior</a>,
    </div>

</div>

@push('js')
    <script>
        document.addEventListener('livewire:initialized', () => {
            function initSelect2() {
                $('#location-select').select2({
                    placeholder: 'Select Location',
                    allowClear: true
                }).on('change', function (e) {
                    @this.set('location', $(this).val());
                });

            }

            initSelect2();

            Livewire.on('reinit-select2', () => {
                initSelect2();
            });
        });
    </script>
@endpush
