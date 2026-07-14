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
            
            {{-- Validation Notice alert --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                    <i class="fi-rr-exclamation me-2 text-xl"></i>
                    <div>
                        <strong>Validation Error:</strong> Please review and fix the marked fields in the form below.
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3">
                    <livewire:pages.employer.components.sidebar />
                </div>

                {{-- Content --}}
                <div class="col-lg-9">

                    {{-- Create / Edit Job Card --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-6 mb-5">
                        
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4 flex-wrap gap-2">
                            <div>
                                <h2 class="text-2xl font-bold text-primary-800 mb-1">
                                    {{ $editing ? 'Edit Job Opening' : 'Post New Job Opening' }}
                                </h2>
                                <p class="text-gray-600 mb-0">
                                    Fill in the fields below to {{ $editing ? 'update this job opening' : 'create a new career opportunity' }}.
                                </p>
                            </div>
                            @if ($editing)
                                <button type="button" wire:click="cancelEdit" class="btn btn-sm btn-outline-secondary font-semibold py-1.5 px-3">
                                    Cancel Editing
                                </button>
                            @endif
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                                <i class="fi-rr-checkbox me-2 text-xl"></i>
                                <div>{{ session('success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form wire:submit="save">
                            <div class="row g-3">

                                {{-- Job Title --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Job Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" wire:model="title" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" placeholder="e.g. Senior Software Engineer" maxlength="255">
                                    @error('title')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Category --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <div wire:ignore x-data="jobFormSelect2(@entangle('job_category_id'), { placeholder: 'Select Category' })">
                                        <select x-ref="select" class="form-control select2-custom">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('job_category_id')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Job Type --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Job Type <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="job_type" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800">
                                        <option value="">Select Job Type</option>
                                        @foreach (\App\Enums\EmploymentType::cases() as $type)
                                            <option value="{{ $type->value }}">
                                                {{ $type->getLabel() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('job_type')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Location --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Location <span class="text-danger">*</span>
                                    </label>
                                    <div wire:ignore x-data="jobFormSelect2(@entangle('location_id'), { placeholder: 'Select Location' })">
                                        <select x-ref="select" class="form-control select2-custom">
                                            <option value="">Select Location</option>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location->id }}">
                                                    {{ $location->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('location_id')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Salary --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Salary Range <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" wire:model="salary_range" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" maxlength="255" placeholder="e.g. AED 5,000 - 7,000">
                                    @error('salary_range')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Experience --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Required Experience <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" wire:model="required_experience" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800" maxlength="255" placeholder="e.g. 2+ years">
                                    @error('required_experience')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Gender --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Gender <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="gender" class="form-control focus:ring-2 focus:ring-primary-200 focus:border-primary-800">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="both">Both</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('gender')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Nationalities --}}
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-semibold text-gray-700">
                                        Expected Nationalities <span class="text-danger">*</span>
                                    </label>
                                    <div wire:ignore x-data="jobFormSelect2(@entangle('expected_nationalities'), { placeholder: 'Select Nationalities', multiple: true })">
                                        <select x-ref="select" class="form-control select2-custom" multiple>
                                            @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality->id }}">
                                                    {{ $nationality->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('expected_nationalities')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                    @error('expected_nationalities.*')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-semibold text-gray-700">
                                        Job Description <span class="text-danger">*</span>
                                    </label>
                                    <div wire:ignore x-data="jobFormEditor(@entangle('description'), '#description-editor', { height: 280, toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link' })">
                                        <textarea id="description-editor"></textarea>
                                    </div>
                                    @error('description')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Responsibilities --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-semibold text-gray-700">
                                        Responsibilities
                                    </label>
                                    <div wire:ignore x-data="jobFormEditor(@entangle('responsibilities'), '#responsibilities-editor', { height: 200 })">
                                        <textarea id="responsibilities-editor"></textarea>
                                    </div>
                                    @error('responsibilities')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Skills --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label font-semibold text-gray-700">
                                        Skills
                                    </label>
                                    <div wire:ignore x-data="jobFormEditor(@entangle('skills'), '#skills-editor', { height: 200 })">
                                        <textarea id="skills-editor"></textarea>
                                    </div>
                                    @error('skills')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Benefits --}}
                                <div class="col-md-12 mb-4">
                                    <label class="form-label font-semibold text-gray-700">
                                        Benefits
                                    </label>
                                    <div wire:ignore x-data="jobFormEditor(@entangle('benefits'), '#benefits-editor', { height: 200 })">
                                        <textarea id="benefits-editor"></textarea>
                                    </div>
                                    @error('benefits')
                                        <small class="text-danger d-block mt-1 font-semibold"><i class="fi-rr-exclamation me-1 text-[10px]"></i>{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Submit button with loader --}}
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-default hover-up d-inline-flex align-items-center gap-2 font-semibold shadow-sm" wire:loading.attr="disabled" wire:target="save">
                                        <span wire:loading.remove wire:target="save">
                                            <i class="fi-rr-paper-plane text-sm"></i>
                                            {{ $editing ? 'Update Job Posting' : 'Post Job Opening' }}
                                        </span>
                                        <span wire:loading wire:target="save">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Saving Changes...
                                        </span>
                                    </button>
                                    @if ($editing)
                                        <button type="button" wire:click="cancelEdit" class="btn btn-secondary font-semibold ms-2 shadow-sm">
                                            Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- My Jobs Card --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-primary-800 mb-4 border-bottom pb-2">
                            My Job Postings
                        </h2>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="bg-gray-50 border-bottom border-gray-100">
                                        <th class="py-3 px-4 text-gray-700 font-semibold">Title</th>
                                        <th class="py-3 px-3 text-gray-700 font-semibold">Category</th>
                                        <th class="py-3 px-3 text-gray-700 font-semibold">Type</th>
                                        <th class="py-3 px-3 text-gray-700 font-semibold">Status</th>
                                        <th class="py-3 px-4 text-gray-700 font-semibold text-end" style="width: 180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jobs as $job)
                                        <tr class="hover:bg-gray-50/50 border-bottom border-gray-100 transition duration-150">
                                            <td class="py-3 px-4 font-bold text-gray-800">
                                                {{ $job->title }}
                                            </td>
                                            <td class="py-3 px-3 text-sm text-gray-600">
                                                {{ $job->job_category?->name }}
                                            </td>
                                            <td class="py-3 px-3 text-sm text-gray-600">
                                                {{ $job->job_type->getLabel() }}
                                            </td>
                                            <td class="py-3 px-3">
                                                @if ($job->status)
                                                    <span class="badge py-1.5 px-3 rounded-full font-bold shadow-sm" style="background-color: #d1fae5; color: #059669;">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="badge py-1.5 px-3 rounded-full font-bold shadow-sm" style="background-color: #fee2e2; color: #dc2626;">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-end">
                                                <button type="button" wire:click="edit({{ $job->id }})" class="btn btn-sm btn-outline-primary border-primary-800 text-primary-800 hover:bg-primary-800 hover:text-white font-semibold py-1 px-3.5 rounded me-2">
                                                    Edit
                                                </button>
                                                <button type="button" wire:click="delete({{ $job->id }})" wire:confirm="Are you sure you want to delete this job?" class="btn btn-sm btn-outline-danger border-danger text-danger hover:bg-danger hover:text-white font-semibold py-1 px-3 rounded">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-gray-500">
                                                <i class="fi-rr-box-open text-3xl mb-2 d-block opacity-40"></i>
                                                No job openings posted yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 px-3 border-top pt-4">
                            {{ $jobs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @once
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/select2-bootstrap-5-theme.min.css') }}">
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
                    window.tinymce ?
                    Promise.resolve() :
                    jobFormLoadScriptOnce('{{ asset("assets/js/tinymce/tinymce.min.js") }}'),
                ]);
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
                        })()
                        ;
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
