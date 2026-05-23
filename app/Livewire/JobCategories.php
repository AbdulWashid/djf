<?php

namespace App\Livewire;

use App\Models\JobCategory;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class JobCategories extends Component
{
    use WithPagination;

    public bool $showAll = false;
    public int $categoryCount = 0;

    public function mount(bool $showAll = false): void
    {
        $this->showAll = $showAll;
    }

    public function render()
    {
        $this->categoryCount = Cache::remember('job_categories_active_count', now()->addMinutes(30), function () {
            return JobCategory::query()
                ->active()
                ->count();
        });

        if ($this->showAll) {
            $categories = JobCategory::query()
                ->active()
                ->select('id', 'name', 'slug', 'logo')
                ->withCount('openings')
                ->orderByDesc('openings_count')
                ->orderBy('name')
                ->paginate(12);
        } else {
            $categories = Cache::remember('job_categories_home', now()->addMinutes(30), function () {
                return JobCategory::query()
                    ->active()
                    ->select('id', 'name', 'slug', 'logo')
                    ->withCount('openings')
                    ->orderByDesc('openings_count')
                    ->orderBy('name')
                    ->limit(7)
                    ->get();
            });
        }

        return view('livewire.job-categories', [
            'categories' => $categories,
        ]);
    }
}
