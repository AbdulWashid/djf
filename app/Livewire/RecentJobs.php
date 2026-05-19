<?php

namespace App\Livewire;

use App\Models\Opening;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class RecentJobs extends Component
{
    public $jobs;



    public function render()
    {
        $this->jobs  = Cache::remember('recent_jobs', now()->addMinutes(30), function () {
            return Opening::where('status', 1)
                ->with('employer')
                ->orderBy('created_at', 'desc')->take(5)->get();
                });

        return view('livewire.recent-jobs');
    }
}
