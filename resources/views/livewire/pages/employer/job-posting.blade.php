<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Opening;
use App\Models\JobCategory;
use App\Models\Nationality;
use App\Models\Location;
use App\Enums\EmploymentType;

new #[Layout('components.frontend.main')] class extends Component {
    use WithPagination;

    public $title = '';
    public $job_category_id = '';

    public $description = '';
    public $responsibilities = '';
    public $skills = '';
    public $benefits = '';

    public $job_type = '';
    public $location_id = '';
    public $locations;
    public $salary_range = '';

    public $gender = '';
    public $required_experience = '';

    public $expected_nationalities = [];

    public $editing = false;
    public $jobId = null;

    public function mount()
    {
        $this->locations = Location::orderBy('name')->get();
    }
    protected function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'job_category_id' => ['required', 'integer', 'exists:job_categories,id'],

            'description' => ['required', 'string', $this->richTextNotEmptyRule()],
            'responsibilities' => ['nullable', 'string'],
            'skills' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],

            'job_type' => ['required', Rule::in(array_column(EmploymentType::cases(), 'value'))],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'salary_range' => ['required', 'string', 'max:255'],

            'gender' => ['required', Rule::in(['male', 'female', 'both', 'other'])],
            'required_experience' => ['required', 'string', 'max:255'],

            'expected_nationalities' => ['required', 'array', 'min:1'],
            'expected_nationalities.*' => ['integer', 'exists:nationalities,id'],
        ];
    }

    protected function messages()
    {
        return [
            'job_category_id.exists' => 'Please select a valid job category.',
            'location_id.exists' => 'Please select a valid location.',
            'expected_nationalities.min' => 'Select at least one expected nationality.',
            'expected_nationalities.*.exists' => 'One or more selected nationalities are invalid.',
        ];
    }

    /**
     * Guard against rich-text fields that only contain empty HTML markup
     * (e.g. "<p><br></p>" left behind by a WYSIWYG editor).
     */
    protected function richTextNotEmptyRule()
    {
        return function ($attribute, $value, $fail) {
            if (trim(strip_tags((string) $value)) === '') {
                $fail('The ' . str_replace('_', ' ', $attribute) . ' field is required.');
            }
        };
    }
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
            'location_id' => $this->location_id,
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
            $data['slug'] = $this->generateUniqueSlug($this->title);
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
        $this->location_id = $job->location_id;
        $this->salary_range = $job->salary_range;

        $this->gender = $job->gender;
        $this->required_experience = $job->required_experience;

        $this->expected_nationalities = $job->expected_nationalities ?? [];

        $this->dispatch('scroll-to-top');
    }
    public function cancelEdit()
    {
        $this->reset(['jobId', 'editing', 'title', 'job_category_id', 'description', 'responsibilities', 'skills', 'benefits', 'job_type', 'location_id', 'salary_range', 'gender', 'required_experience', 'expected_nationalities']);

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
            'categories' => JobCategory::where('status', true)->orderBy('name')->get(),
            'nationalities' => Nationality::where('status', true)->orderBy('name')->get(),

            'jobs' => Opening::with(['job_category', 'location'])
                ->where('employer_id', auth('employer')->id())
                ->latest()
                ->paginate(10),
        ];
    }

    protected function generateUniqueSlug(string $title): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $original = $slug;
        $count = 1;

        while (\App\Models\Opening::where('slug', $slug)->exists()) {
            $count++;
            $slug = $original . '-' . $count;
        }

        return $slug;
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

                                    <input type="text" wire:model="title" class="form-control" maxlength="255">

                                    @error('title')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Category --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Category <span class="text-danger">*</span>
                                    </label>

                                    <div wire:ignore x-data="jobFormSelect2(@entangle('job_category_id'), { placeholder: 'Select Category' })">
                                        <select x-ref="select" class="form-control">
                                            <option value="">Select Category</option>

                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

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

                                    <div wire:ignore x-data="jobFormSelect2(@entangle('location_id'), { placeholder: 'Select Location' })">
                                        <select x-ref="select" class="form-control">
                                            <option value="">Select Location</option>

                                            @foreach ($locations as $location)
                                                <option value="{{ $location->id }}">
                                                    {{ $location->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @error('location_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Salary --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Salary Range <span class="text-danger">*</span>
                                    </label>

                                    <input type="text" wire:model="salary_range" class="form-control" maxlength="255"
                                        placeholder="e.g. AED 5,000 - 7,000">

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
                                        maxlength="255" placeholder="e.g. 2+ years">

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

                                    <div wire:ignore x-data="jobFormSelect2(@entangle('expected_nationalities'), { placeholder: 'Select Nationalities', multiple: true })">
                                        <select x-ref="select" class="form-control" multiple>
                                            @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality->id }}">
                                                    {{ $nationality->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @error('expected_nationalities')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                    @error('expected_nationalities.*')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        Job Description <span class="text-danger">*</span>
                                    </label>

                                    <div wire:ignore x-data="jobFormEditor(@entangle('description'), '#description-editor', { height: 280, toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link' })">
                                        <textarea id="description-editor"></textarea>
                                    </div>

                                    @error('description')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Responsibilities --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        Responsibilities
                                    </label>

                                    <div wire:ignore x-data="jobFormEditor(@entangle('responsibilities'), '#responsibilities-editor', { height: 240 })">
                                        <textarea id="responsibilities-editor"></textarea>
                                    </div>

                                    @error('responsibilities')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Skills --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">
                                        Skills
                                    </label>

                                    <div wire:ignore x-data="jobFormEditor(@entangle('skills'), '#skills-editor', { height: 240 })">
                                        <textarea id="skills-editor"></textarea>
                                    </div>

                                    @error('skills')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Benefits --}}
                                <div class="col-md-12 mb-4">
                                    <label class="form-label">
                                        Benefits
                                    </label>

                                    <div wire:ignore x-data="jobFormEditor(@entangle('benefits'), '#benefits-editor', { height: 240 })">
                                        <textarea id="benefits-editor"></textarea>
                                    </div>

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
                                                {{ $job->job_type->getLabel() }}
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
                        <div class="mt-4 px-3">
                            {{ $jobs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @once
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
        <script>
            function jobFormLoadScriptOnce(src) {
                return new Promise((resolve, reject) => {
                    if (document.querySelector(`script[src="${src}"]`)) {
                        resolve();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = src;
                    script.onload = () => resolve();
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            window.jobFormDependencies = window.jobFormDependencies || (async function() {
                await Promise.all([
                    window.jQuery ?
                    Promise.resolve() :
                    jobFormLoadScriptOnce('https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js'),
                    window.tinymce ?
                    Promise.resolve() :
                    jobFormLoadScriptOnce('https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js'),
                ]);

                if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) {
                    await jobFormLoadScriptOnce(
                        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
                }
            })();

            function jobFormSelect2(initialValue, options) {
                options = options || {};

                return {
                    value: initialValue,
                    init() {
                        (async () => {
                            await window.jobFormDependencies;

                            const select = jQuery(this.$refs.select);
                            const empty = options.multiple ? [] : '';

                            select.select2({
                                width: '100%',
                                theme: 'bootstrap-5',
                                placeholder: options.placeholder || '',
                                allowClear: options.multiple ? false : true,
                            });

                            select.val(this.value ?? empty).trigger('change.select2');

                            select.on('change', () => {
                                this.value = select.val() ?? empty;
                            });

                            this.$watch('value', (newValue) => {
                                const current = select.val() ?? empty;
                                const incoming = newValue ?? empty;
                                const changed = options.multiple ?
                                    JSON.stringify(current) !== JSON.stringify(incoming) :
                                    current != incoming;

                                if (changed) {
                                    select.val(incoming).trigger('change.select2');
                                }
                            });
                        })
                        ();
                    },
                };
            }

            function jobFormEditor(initialValue, selector, options) {
                options = options || {};

                return {
                    value: initialValue,
                    editor: null,
                    init() {
                        (async () => {
                            await window.jobFormDependencies;

                            tinymce.init({
                                selector: selector,
                                height: options.height || 240,
                                menubar: false,
                                plugins: 'lists link',
                                toolbar: options.toolbar || 'bold italic | bullist numlist | link',
                                branding: false,
                                setup: (editor) => {
                                    this.editor = editor;

                                    editor.on('init', () => {
                                        editor.setContent(this.value || '');
                                    });

                                    editor.on('change keyup undo redo', () => {
                                        this.value = editor.getContent();
                                    });
                                },
                            });

                            this.$watch('value', (newValue) => {
                                if (this.editor && this.editor.getContent() !== (newValue || '')) {
                                    this.editor.setContent(newValue || '');
                                }
                            });
                        })();
                    },
                };
            }
        </script>
    @endonce

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
