<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ config('app.name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{-- {!! $slot !!} --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {!! $subcopy !!}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            {{-- © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }} --}}
            {{ app(\App\Settings\MailSettings::class)->footer_text }}
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
