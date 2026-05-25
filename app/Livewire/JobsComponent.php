<?php
// correct
namespace App\Livewire;

use Illuminate\Support\Str;
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

    public $location;
    public $category;
    public $job_type = [];
    public $locations;
    public $salary_range;
    public $categories;
    public $job_types;
    public $salary_ranges;
    public $q;
    public $pageTitle = 'Urgent {Category} Jobs in {location} | Dubaijobfinder';
    public $pageDescription = 'Find the latest {Category} jobs in {location}. Apply online for urgent vacancies and career opportunities on Dubaijobfinder.';

    public function mount($location = null, $category_slug = null): void
    {
        $this->categories = JobCategory::active()->pluck('name', 'id');
        $this->locations = Opening::distinct()->pluck('location');
        $this->job_types = EmploymentType::toOptionsArray();
        $this->q = request()->query('q');

        if ($location) {
            $cat = JobCategory::where('slug', Str::slug($location))->first();
            if ($cat) {
                $this->category = $cat->id;
                $this->location = null;
            } else {
                $foundLocation = $this->locations->first(function ($loc) use ($location) {
                    return Str::slug($loc) === Str::slug($location);
                });
                $this->location = Str::slug($foundLocation) ? $foundLocation : null;
            }
        }
        if ($category_slug) {
            $foundLocation = $this->locations->first(function ($loc) use ($location) {
                return Str::slug($loc) === Str::slug($location);
            });
            $this->location = $foundLocation;

            $cat = JobCategory::where('slug', Str::slug($category_slug))->first();
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
        $locationSlug = $this->location ? Str::slug($this->location) : null;
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
        $this->dispatch('seo-updated', [
            'title' => str_replace(
                ['{location}', '{Category}'],
                [Str::title($this->location ?? 'Dubai'), $this->category ? JobCategory::find($this->category)?->name ?? '' : ''],
                $this->pageTitle
            ),
            'description' => str_replace(
                ['{location}', '{Category}'],
                [Str::title($this->location ?? 'Dubai'), $this->category ? JobCategory::find($this->category)?->name ?? '' : ''],
                $this->pageDescription
            ),
        ]);
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
        //    dd($this->jobs->items());
        return view('livewire.jobs-component', ['jobs' => $this->jobs])->layout('components.frontend.main', [
            'pageTitle' => str_replace(['{location}', '{Category}'], [Str::title($this->location ?? 'Dubai'), $this->category ? JobCategory::find($this->category)->name : ''], $this->pageTitle),
            'pageDescription' => str_replace(['{location}', '{Category}'], [Str::title($this->location ?? 'Dubai'), $this->category ? JobCategory::find($this->category)->name : ''], $this->pageDescription),
        ]);
    }

    public function search()
    {
        $query = Opening::query()->active()->with('employer');

        $keyword = trim($this->q ?: request()->query('q', ''));

        // Keyword filter: search by job title or employer name
        // if ($keyword !== '') {
        //     $query->where(function ($q) use ($keyword) {
        //         $q->where('title', 'like', "%{$keyword}%")->orWhereHas('employer', function ($q2) use ($keyword) {
        //             $q2->where('name', 'like', "%{$keyword}%");
        //         });
        //     });
        // }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('job_type', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        if ($this->location) {
            $query->where('location', ucwords(str_replace('-', ' ', $this->location)));
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
