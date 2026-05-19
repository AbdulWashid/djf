<?php

namespace App\Livewire;

use AllowDynamicProperties;
use App\Enums\EmploymentType;
use App\Models\JobCategory;
use App\Models\Opening;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use function Laravel\Prompts\search;

#[AllowDynamicProperties]
class JobsComponent extends Component
{
    use WithPagination;

    // url query string


//    public $jobs;
    public $location;
    public $category;
    public $job_type = [];
    public $locations;
    public $salary_range;
    public $categories;
    public $job_types;
    public $salary_ranges;

    public $i = 1;




    public function mount($category_slug = null): void
    {
        if ($category_slug) {
            $cat = JobCategory::where('slug', $category_slug)->first();
            if ($cat) {
                $this->category = $cat->id;
            }
        }

//        $this->jobs = $this->search();
        $this->categories = JobCategory::active()->pluck('name', 'id');
        $this->locations = Opening::distinct()->pluck('location');
        // Get job types from EmploymentType enum
        $this->job_types = EmploymentType::toOptionsArray();
    }


    public function clear()
    {
        // reset component search filters
        $this->location = null;
        $this->category = null;
        $this->job_type = [];
        $this->salary_range = null;
        $this->jobs = $this->search();
        $this->resetPage();
        $this->dispatch('reinit-select2');
    }

    public function render()
    {

        $this->jobs = $this->search();
//        dd($job_list);
        return view('livewire.jobs-component',
            ['jobs' => $this->jobs])->layout('components.frontend.main');
    }

    public function search()
    {

//        dump($this->job_type);
//        dump("Location: ", $this->location);
//        dump($this->category);
//        dump($this->something);
//dd();
        $query = Opening::query();
        if ($this->location) {
            $query->where('location', 'like', '%' . $this->location . '%');
        }
        if ($this->category) {
            $query->where('job_category_id', $this->category);
        }
        if ($this->job_type) {
            $query->whereIn('job_type', (array)$this->job_type);
        }
//        if ($this->salary_range) {
//            $query->where('salary_range', $this->salary_range);
//        }
        $this->dispatch('reinit-select2');
        return $query->paginate(10);
    }


}
