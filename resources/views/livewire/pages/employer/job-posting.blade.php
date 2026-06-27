<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Opening;
use App\Models\JobCategory;
use App\Models\Nationality;

new #[Layout('components.frontend.main')] class extends Component {
    use WithPagination;

    public $title = '';
    public $job_category_id = '';

    public $description = '';
    public $responsibilities = '';
    public $skills = '';
    public $benefits = '';

    public $job_type = '';
    public $location = '';
    public $salary_range = '';

    public $gender = '';
    public $required_experience = '';

    public $expected_nationalities = [];

    public $editing = false;
    public $jobId = null;

    protected function rules()
    {
        return [
            'title' => 'required|max:255',
            'job_category_id' => 'required|exists:job_categories,id',

            'description' => 'required',
            'responsibilities' => 'nullable',
            'skills' => 'nullable',
            'benefits' => 'nullable',

            'job_type' => 'required',
            'location' => 'required|max:255',
            'salary_range' => 'required|max:255',

            'gender' => 'required',
            'required_experience' => 'required',

            'expected_nationalities' => 'required|array|min:1',
        ];
    }
    // public function save()
    // {
    //     $this->validate([
    //         'title' => 'required|max:255',
    //         'job_category_id' => 'required|exists:job_categories,id',

    //         'description' => 'required',

    //         'responsibilities' => 'nullable',
    //         'skills' => 'nullable',
    //         'benefits' => 'nullable',

    //         'job_type' => 'required',
    //         'location' => 'required|max:255',
    //         'salary_range' => 'required|max:255',

    //         'gender' => 'required',
    //         'required_experience' => 'required',

    //         'expected_nationalities' => 'required|array|min:1',
    //     ]);

    //     Opening::create([
    //         'employer_id' => auth('employer')->id(),
    //         'job_category_id' => $this->job_category_id,

    //         'title' => $this->title,
    //         'slug' => Str::slug($this->title) . '-' . time(),

    //         'description' => $this->description,
    //         'responsibilities' => $this->responsibilities,
    //         'skills' => $this->skills,
    //         'benefits' => $this->benefits,

    //         'job_type' => $this->job_type,
    //         'location' => $this->location,
    //         'salary_range' => $this->salary_range,

    //         'expected_nationalities' => $this->expected_nationalities,
    //         'gender' => $this->gender,
    //         'required_experience' => $this->required_experience,

    //         'featured' => false,
    //         'status' => false,
    //     ]);

    //     $this->reset(['title', 'job_category_id', 'description', 'responsibilities', 'skills', 'benefits', 'job_type', 'location', 'salary_range', 'gender', 'required_experience', 'expected_nationalities']);

    //     $this->resetPage();

    //     session()->flash('success', 'Job posted successfully.');
    // }
    public function save()
    {
        $this->validate();

        $data = [
            'job_category_id' => $this->job_category_id,

            'title' => $this->title,
            'description' => $this->description,
            'responsibilities' => $this->responsibilities,
            'skills' => $this->skills,
            'benefits' => $this->benefits,

            'job_type' => $this->job_type,
            'location' => $this->location,
            'salary_range' => $this->salary_range,

            'expected_nationalities' => $this->expected_nationalities,
            'gender' => $this->gender,
            'required_experience' => $this->required_experience,
        ];

        if ($this->editing) {
            Opening::where('employer_id', auth('employer')->id())
                ->findOrFail($this->jobId)
                ->update($data);

            session()->flash('success', 'Job updated successfully.');
        } else {
            $data['employer_id'] = auth('employer')->id();
            $data['slug'] = Str::slug($this->title) . '-' . time();
            $data['featured'] = false;
            $data['status'] = false;

            Opening::create($data);

            session()->flash('success', 'Job posted successfully.');
        }

        $this->cancelEdit();
    }

    public function edit($id)
    {
        $job = Opening::where('employer_id', auth('employer')->id())->findOrFail($id);

        $this->jobId = $job->id;
        $this->editing = true;

        $this->title = $job->title;
        $this->job_category_id = $job->job_category_id;

        $this->description = $job->description;
        $this->responsibilities = $job->responsibilities;
        $this->skills = $job->skills;
        $this->benefits = $job->benefits;

        $this->job_type = $job->job_type;
        $this->location = $job->location;
        $this->salary_range = $job->salary_range;

        $this->gender = $job->gender;
        $this->required_experience = $job->required_experience;

        $this->expected_nationalities = $job->expected_nationalities ?? [];

        $this->dispatch('scroll-to-top');
    }
    public function cancelEdit()
    {
        $this->reset(['jobId', 'editing', 'title', 'job_category_id', 'description', 'responsibilities', 'skills', 'benefits', 'job_type', 'location', 'salary_range', 'gender', 'required_experience', 'expected_nationalities']);

        $this->resetPage();
    }
    public function delete($id)
    {
        Opening::where('employer_id', auth('employer')->id())
            ->findOrFail($id)
            ->delete();
    }

    public function with()
    {
        return [
            'categories' => JobCategory::where('status', true)->get(),

            'jobs' => Opening::query()
                ->where('employer_id', auth('employer')->id())
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>
<div>
    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li>Employer</li>
                <li>Job Posting</li>
            </ul>
        </div>
    </div>

    <section class="mt-20 mb-50">
        <div class="container">
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <strong>Please fix the following errors:</strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">

                {{-- Sidebar --}}
                <div class="col-lg-3">
                    <livewire:pages.employer.components.sidebar />
                </div>

                {{-- Content --}}
                <div class="col-lg-9">

                    {{-- Create Job Card --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-6 mb-5">

                        <h2 class="text-2xl font-bold text-primary-800 mb-2">
                            {{ $editing ? 'Edit Job' : 'Post New Job' }}
                        </h2>

                        <p class="text-gray-600 mb-5">
                            Create a new job opening.
                        </p>

                        @if (session('success'))
                            <div class="alert alert-success mb-4">
                                {{ session('success') }}
                            </div>
                        @endif
                        <form wire:submit="save">
                            <div class="row">

                                {{-- Job Title --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Job Title <span class="text-danger">*</span>
                                    </label>

                                    <input type="text" wire:model="title" class="form-control"
                                        placeholder="Senior Laravel Developer">

                                    @error('title')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Category --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Category <span class="text-danger">*</span>
                                    </label>

                                    <select wire:model="job_category_id" class="form-control">
                                        <option value="">Select Category</option>

                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('job_category_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Job Type --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Job Type <span class="text-danger">*</span>
                                    </label>

                                    <select wire:model="job_type" class="form-control">
                                        <option value="">Select Job Type</option>

                                        @foreach (\App\Enums\EmploymentType::cases() as $type)
                                            <option value="{{ $type->value }}">
                                                {{ $type->getLabel() }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('job_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Location --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Location <span class="text-danger">*</span>
                                    </label>

                                    <input type="text" wire:model="location" class="form-control"
                                        placeholder="Dubai">

                                    @error('location')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Salary --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Salary Range <span class="text-danger">*</span>
                                    </label>

                                    <input type="text" wire:model="salary_range" class="form-control"
                                        placeholder="AED 5,000 - 8,000">

                                    @error('salary_range')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Experience --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Required Experience <span class="text-danger">*</span>
                                    </label>

                                    <input type="text" wire:model="required_experience" class="form-control"
                                        placeholder="2+ Years">

                                    @error('required_experience')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Gender --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Gender <span class="text-danger">*</span>
                                    </label>

                                    <select wire:model="gender" class="form-control">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="both">Both</option>
                                        <option value="other">Other</option>
                                    </select>

                                    @error('gender')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Nationalities --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Expected Nationalities <span class="text-danger">*</span>
                                    </label>

                                    <select wire:model="expected_nationalities" class="form-control" multiple>

                                        @foreach (\App\Models\Nationality::orderBy('name')->get() as $nationality)
                                            <option value="{{ $nationality->id }}">
                                                {{ $nationality->name }}
                                            </option>
                                        @endforeach

                                    </select>

                                    <small class="text-muted">
                                        Hold Ctrl (Windows) / Cmd (Mac) to select multiple.
                                    </small>

                                    @error('expected_nationalities')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        Job Description <span class="text-danger">*</span>
                                    </label>

                                    <textarea rows="6" wire:model="description" class="form-control" placeholder="Describe the job role..."></textarea>

                                    @error('description')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Responsibilities --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        Responsibilities
                                    </label>

                                    <textarea rows="5" wire:model="responsibilities" class="form-control" placeholder="List job responsibilities..."></textarea>

                                    @error('responsibilities')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Skills --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        Skills
                                    </label>

                                    <textarea rows="5" wire:model="skills" class="form-control" placeholder="Required skills..."></textarea>

                                    @error('skills')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Benefits --}}
                                <div class="col-md-12 mb-4">
                                    <label class="form-label">
                                        Benefits
                                    </label>

                                    <textarea rows="5" wire:model="benefits" class="form-control"
                                        placeholder="Salary, Visa, Insurance, Accommodation etc..."></textarea>

                                    @error('benefits')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Submit --}}
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-default hover-up"
                                        wire:loading.attr="disabled" wire:target="save">

                                        <span wire:loading.remove wire:target="save">
                                            <i class="fi-rr-paper-plane me-2"></i>
                                            {{ $editing ? 'Update Job' : 'Post Job' }}
                                        </span>

                                        <span wire:loading wire:target="save">
                                            Saving...
                                        </span>
                                    </button>
                                    @if ($editing)
                                        <button type="button" wire:click="cancelEdit"
                                            class="btn btn-secondary ms-2">

                                            Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- My Jobs Card --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-6">

                        <h2 class="text-2xl font-bold text-primary-800 mb-4">
                            My Job Postings
                        </h2>

                        <div class="table-responsive">

                            <table class="table">

                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th width="150">Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($jobs as $job)
                                        <tr>

                                            <td>
                                                {{ $job->title }}
                                            </td>

                                            <td>
                                                {{ $job->job_category?->name }}
                                            </td>

                                            <td>
                                                {{ $job->job_type }}
                                            </td>

                                            <td>

                                                @if ($job->status)
                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        Inactive
                                                    </span>
                                                @endif

                                            </td>

                                            <td>

                                                <button type="button" wire:click="edit({{ $job->id }})"
                                                    class="text-primary me-2">

                                                    Edit
                                                </button>

                                                <button wire:click="delete({{ $job->id }})"
                                                    wire:confirm="Are you sure you want to delete this job?"
                                                    class="text-danger">

                                                    Delete

                                                </button>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                No jobs found.
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                        <div class="mt-4">
                            {{ $jobs->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    @script
        <script>
            $wire.on('scroll-to-top', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        </script>
    @endscript
</div>
