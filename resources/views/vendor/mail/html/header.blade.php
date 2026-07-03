@php
    $settings = app(\App\Settings\MailSettings::class);
@endphp
@props(['url'])
<tr>
    <td class="header">
        {{-- <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
            @else
                {!! $slot !!}
            @endif
        </a> --}}
        <a href="{{ config('app.url') }}" style="display:inline-block">
            @if ($settings->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo_path) }}"
                    alt="{{ $settings->from_name }}" style="max-height:70px; display:block;">
            @else
                {{ $settings->from_name }}
            @endif
        </a>
    </td>
</tr>
