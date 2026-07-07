<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold">System Cache</h3>
                <p class="text-sm text-gray-500">Clear all application, config, view, route, and Filament caches.</p>
            </div>

            <x-filament::button color="danger" icon="heroicon-o-arrow-path" wire:click="mountAction('clearCache')">
                Clear Cache
            </x-filament::button>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
