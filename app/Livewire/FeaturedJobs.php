<?php

namespace App\Livewire;

use App\Models\Opening;
use Livewire\Component;

class FeaturedJobs extends Component
{
    public $jobs;

    public function render()
    {
        $this->jobs = rememberIfEnabled('feature_jobs', now()->addMinutes(30), function () {
            return Opening::select('id', 'employer_id', 'title', 'slug', 'location', 'job_type', 'created_at', 'salary_range', 'description')
                ->where('featured', 1)
                ->with([
                    'employer' => function ($query) {
                        $query->select('id', 'logo', 'name');
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();
        });

        return view('livewire.featured-jobs');
    }
}
