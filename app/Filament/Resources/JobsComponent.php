<?php
// correct
namespace App\Filament\Resources;

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
    public $q;
    public $i = 1;

    public function mount($location = null, $category_slug = null): void
    {
        $this->categories = JobCategory::active()->pluck('name', 'id');
        $this->locations = Opening::distinct()->pluck('location');
        $this->job_types = EmploymentType::toOptionsArray();
        $this->q = request()->query('q');
        
        if ($location && !$category_slug) {
            // Check if it's a category slug instead of a location (for BC)
            $cat = JobCategory::where('slug', $location)->first();
            if ($cat) {
                $this->category = $cat->id;
                $this->location = null;
            } else {
                // If it's not a category, treat it as location.
                $originalLocation = str_replace('-', ' ', $location);
                $foundLocation = $this->locations->first(function ($loc) use ($originalLocation) {
                    return strtolower($loc) === strtolower($originalLocation);
                });
                $this->location = $foundLocation ?: $originalLocation;
            }
        } elseif ($location && $category_slug) {
            $originalLocation = str_replace('-', ' ', $location);
            $foundLocation = $this->locations->first(function ($loc) use ($originalLocation) {
                return strtolower($loc) === strtolower($originalLocation);
            });
            $this->location = $foundLocation ?: $originalLocation;

            $cat = JobCategory::where('slug', $category_slug)->first();
            if ($cat) {
                $this->category = $cat->id;
            }
        }
    }

    public function hydrate(): void
    {
        $this->q = request()->query('q', $this->q);
    }

    public function updatedLocation($value)
    {
        $this->updateUrl();
    }

    public function updatedCategory($value)
    {
        $this->updateUrl();
    }

    private function updateUrl()
    {
        $locationSlug = $this->location ? strtolower(str_replace(' ', '-', $this->location)) : null;
        $categorySlug = null;

        if ($this->category) {
            $cat = JobCategory::find($this->category);
            $categorySlug = $cat?->slug;
        }

        $url = route('jobs');
        if ($locationSlug && $categorySlug) {
            $url = route('jobs.location.category', ['location' => $locationSlug, 'category_slug' => $categorySlug]);
        } elseif ($locationSlug) {
            $url = route('jobs.location', ['location' => $locationSlug]);
        } elseif ($categorySlug) {
            $url = route('jobs.category', ['category' => $categorySlug]);
        }

        $url = rtrim($url, '/') . '/';
        if (!empty($this->q)) {
            $url .= '?q=' . urlencode(trim($this->q));
        }

        $this->dispatch('url-updated', ['url' => $url]);
    }

    public function clear()
    {
        // reset component search filters
        $this->location = null;
        $this->category = null;
        $this->job_type = [];
        $this->salary_range = null;
        $this->q = null;
        $this->updateUrl();
        $this->jobs = $this->search();
        $this->resetPage();
        $this->dispatch('reset-select2');
    }

    public function render()
    {
        $this->jobs = $this->search();
        //        dd($job_list);
        return view('livewire.jobs-component', ['jobs' => $this->jobs])->layout('components.frontend.main');
    }

    public function search()
    {
        $query = Opening::query()->active()->with('employer');
        
        // Keyword filter: search by job title or employer name
        $keyword = trim($this->q ?: request()->query('q', ''));

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhereHas('employer', function ($q2) use ($keyword) {
                      $q2->where('name', 'like', "%{$keyword}%");
                  });
            });
        }
        
        if ($this->location) {
            $query->where('location', $this->location);
        }
        
        if ($this->category) {
            $query->where('job_category_id', $this->category);
        }
        
        if ($this->job_type) {
            $query->whereIn('job_type', (array) $this->job_type);
        }

        return $query->paginate(10);
    }
}
