<?php

namespace App\Livewire;

use App\Models\Opening;
use Livewire\Component;

class HomepageSearch extends Component
{
    public string $q = '';
    public ?string $location = null;
    public $locations;

    public function mount(): void
    {
        // Populate distinct locations from openings (non-null), deduplicated
        $this->locations = Opening::query()
            ->whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->filter()
            ->values();
    }

    public function submit()
    {
        // If a location is selected, redirect to SEO URL with a trailing slash
        // and include `q` as query string when present.
        $q = $this->q ?: null;


        if ($this->location) {
            $locationSlug = strtolower(str_replace(' ', '-', $this->location));
            $base = route('jobs.seo', ['location' => $locationSlug]) . '/';
            $url = $q !== null ? $base . ('?q=' . urlencode($q)) : $base;
            return redirect()->to($url);
        }

        // Otherwise, redirect to generic jobs listing; preserve q when provided
        $base = route('jobs') . '/';
        $url = $q !== null ? $base . ('?q=' . urlencode($q)) : $base;
        return redirect()->to($url);
    }
    public function render()
    {
        return view('livewire.homepage-search');
    }
}
