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
    public $job_type = '';
    public $location = '';
    public $salary_range = '';
    public $gender = '';
    public $required_experience = '';
    public $expected_nationalities = [];

    public function save()
    {
        $this->validate([
            'title' => ['required'],
            'job_category_id' => ['required'],
            'description' => ['required'],
            'job_type' => ['required'],
            'location' => ['required'],
            'salary_range' => ['required'],
            'gender' => ['required'],
            'required_experience' => ['required'],
        ]);

        Opening::create([
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . time(),

            'employer_id' => auth('employer')->id(),

            'job_category_id' => $this->job_category_id,

            'description' => $this->description,

            'job_type' => $this->job_type,

            'location' => $this->location,

            'salary_range' => $this->salary_range,

            'gender' => $this->gender,

            'required_experience' => $this->required_experience,

            'expected_nationalities' => $this->expected_nationalities,

            'status' => true,
        ]);

        $this->reset(['title', 'job_category_id', 'description', 'job_type', 'location', 'salary_range', 'gender', 'required_experience', 'expected_nationalities']);

        session()->flash('success', 'Job posted successfully.');
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
                            Post New Job
                        </h2>

                        <p class="text-gray-600 mb-5">
                            Create a new job opening.
                        </p>

                        @if (session('success'))
                            <div class="alert alert-success mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Title</label>
                                <input type="text" wire:model="title" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>

                                <select wire:model="job_category_id" class="form-control">

                                    <option value="">
                                        Select Category
                                    </option>

                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>

                                <textarea rows="5" wire:model="description" class="form-control"></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Type</label>

                                <select wire:model="job_type" class="form-control">

                                    <option value="">Select</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Remote">Remote</option>

                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>

                                <input type="text" wire:model="location" class="form-control">
                            </div>

                        </div>

                        <button wire:click="save" class="btn btn-default hover-up">

                            Post Job

                        </button>

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

                                                <a href="#" class="text-primary-600 me-2">

                                                    Edit

                                                </a>

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
</div>
