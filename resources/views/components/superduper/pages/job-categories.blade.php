@php
    $pageTitle = 'Job Categories';
    $pageDescription = 'Browse all job categories with pagination.';
@endphp

<x-frontend.main :page-title="$pageTitle" :page-description="$pageDescription">
    <div class="pt-40 pb-20">
        <livewire:job-categories :show-all="true" />
    </div>
</x-frontend.main>