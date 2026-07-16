<footer class="mt-auto w-full border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 text-sm md:flex-row">
        <div class="text-gray-600 dark:text-gray-400">
            <a href="/" class="font-medium hover:underline">
                {{ config('app.name') }}
            </a>

            @if (config('app.version'))
                <span class="ml-1">v{{ config('app.version') }}</span>
            @endif
        </div>

        <div class="text-gray-600 dark:text-gray-400">
            © {{ now()->year }} All Rights Reserved.
        </div>
    </div>
</footer>
