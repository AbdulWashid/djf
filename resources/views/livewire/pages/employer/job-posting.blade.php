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

            'gender' => ['required', Rule::in(['Male', 'Female', 'Both (Male/Female)', 'Other'])],
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

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Validation Notice alert --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-start gap-3">
                <i class="fi-rr-exclamation mt-0.5 text-lg"></i>
                <div>
                    <strong class="font-bold">Validation Error:</strong> Please review and fix the marked fields in the
                    form below.
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <livewire:pages.employer.components.sidebar />
            </div>

            {{-- Content --}}
            <div class="lg:col-span-3">
                {{-- Create / Edit Job Card --}}
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 sm:p-8 mb-8">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-5 mb-6 gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                {{ $editing ? 'Edit Job Opening' : 'Post New Job Opening' }}
                            </h2>
                            <p class="text-gray-500 text-sm mt-1">
                                Fill in the fields below to
                                {{ $editing ? 'update this job opening' : 'create a new career opportunity' }}.
                            </p>
                        </div>
                        @if ($editing)
                            <button type="button" wire:click="cancelEdit"
                                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 hover:border-gray-400 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                                Cancel Editing
                            </button>
                        @endif
                    </div>

                    @if (session('success'))
                        <div
                            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
                            <i class="fi-rr-checkbox text-lg"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form wire:submit="save" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Job Title --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Job Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="title"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    placeholder="e.g. Senior Software Engineer" maxlength="255">
                                @error('title')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Category --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="job_category_id"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('job_category_id')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Job Type --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Job Type <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="job_type"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                    <option value="">Select Job Type</option>
                                    @foreach (\App\Enums\EmploymentType::cases() as $type)
                                        <option value="{{ $type->value }}">
                                            {{ $type->getLabel() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('job_type')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Location --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Location <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="location_id"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                    <option value="">Select Location</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}">
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Salary --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Salary Range <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="salary_range"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    maxlength="255" placeholder="e.g. AED 5,000 - 7,000">
                                @error('salary_range')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Experience --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Required Experience <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="required_experience"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition"
                                    maxlength="255" placeholder="e.g. 2+ years">
                                @error('required_experience')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Gender <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="gender"
                                    class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm transition">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Both (Male/Female)">Both (Male/Female)</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('gender')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Expected Nationalities (Scrollable Checkbox Grid) --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Expected Nationalities <span class="text-red-500">*</span>
                                </label>
                                <div
                                    class="block w-full border border-gray-300 rounded-lg p-3 bg-gray-50/50 shadow-sm max-h-[140px] overflow-y-auto">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach ($nationalities as $nationality)
                                            <label
                                                class="inline-flex items-center space-x-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900 select-none">
                                                <input type="checkbox" wire:model="expected_nationalities"
                                                    value="{{ $nationality->id }}"
                                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-4 h-4">
                                                <span>{{ $nationality->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                @error('expected_nationalities')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                                @error('expected_nationalities.*')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Job Description <span class="text-red-500">*</span>
                                </label>
                                <div wire:ignore x-data="jobFormEditor(@entangle('description'), '#description-editor', { height: 280, toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link' })">
                                    <textarea id="description-editor" class="w-full min-h-[200px] border border-gray-300 rounded-lg p-3"></textarea>
                                </div>
                                @error('description')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Responsibilities --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Responsibilities
                                </label>
                                <div wire:ignore x-data="jobFormEditor(@entangle('responsibilities'), '#responsibilities-editor', { height: 200 })">
                                    <textarea id="responsibilities-editor" class="w-full min-h-[150px] border border-gray-300 rounded-lg p-3"></textarea>
                                </div>
                                @error('responsibilities')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Skills --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Skills
                                </label>
                                <div wire:ignore x-data="jobFormEditor(@entangle('skills'), '#skills-editor', { height: 200 })">
                                    <textarea id="skills-editor" class="w-full min-h-[150px] border border-gray-300 rounded-lg p-3"></textarea>
                                </div>
                                @error('skills')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Benefits --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Benefits
                                </label>
                                <div wire:ignore x-data="jobFormEditor(@entangle('benefits'), '#benefits-editor', { height: 200 })">
                                    <textarea id="benefits-editor" class="w-full min-h-[150px] border border-gray-300 rounded-lg p-3"></textarea>
                                </div>
                                @error('benefits')
                                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit button --}}
                        <div class="pt-4 border-t border-gray-100 flex items-center gap-3">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg shadow-sm text-white bg-primary-800 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                                    <i class="fi-rr-paper-plane text-base"></i>
                                    {{ $editing ? 'Update Job Posting' : 'Post Job Opening' }}
                                </span>
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <span
                                        class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    Saving...
                                </span>
                            </button>
                            @if ($editing)
                                <button type="button" wire:click="cancelEdit"
                                    class="inline-flex items-center justify-center px-5 py-3 border border-gray-300 hover:border-gray-400 text-base font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                                    Cancel
                                </button>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- My Jobs Card --}}
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">
                        My Job Postings
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" style="min-width: 700px;">
                            <thead>
                                <tr
                                    class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="py-4 px-6">Title</th>
                                    <th class="py-4 px-4">Category</th>
                                    <th class="py-4 px-4">Type</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-6 text-end" style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                                @forelse($jobs as $job)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-4 px-6 font-bold text-gray-900">
                                            {{ $job->title }}
                                        </td>
                                        <td class="py-4 px-4">
                                            {{ $job->job_category?->name }}
                                        </td>
                                        <td class="py-4 px-4">
                                            {{ $job->job_type->getLabel() }}
                                        </td>
                                        <td class="py-4 px-4">
                                            @if ($job->status)
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">
                                                    Active
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border bg-rose-50 text-rose-700 border-rose-200">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-end">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" wire:click="edit({{ $job->id }})"
                                                    class="inline-flex items-center px-3 py-1.5 border border-primary-100 hover:border-primary-200 text-xs font-bold rounded-lg text-primary-700 bg-primary-50 hover:bg-primary-100 transition shadow-sm">
                                                    Edit
                                                </button>
                                                <button type="button" wire:click="delete({{ $job->id }})"
                                                    wire:confirm="Are you sure you want to delete this job?"
                                                    class="inline-flex items-center px-3 py-1.5 border border-red-100 hover:border-red-200 text-xs font-bold rounded-lg text-red-700 bg-red-50 hover:bg-red-100 transition shadow-sm">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 px-6">
                                            <div
                                                class="flex flex-col items-center justify-center text-center max-w-sm mx-auto">
                                                <div
                                                    class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center text-2xl mb-4 border border-gray-100">
                                                    <i class="fi-rr-box-open"></i>
                                                </div>
                                                <h4 class="font-bold text-gray-800 text-base">
                                                    No job openings posted yet.
                                                </h4>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($jobs->hasPages())
                        <div class="p-6 border-t border-gray-100 bg-gray-50/50 mt-4 rounded-b-xl">
                            {{ $jobs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @once
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
                    jobFormLoadScriptOnce('{{ asset('assets/js/tinymce/tinymce.min.js') }}'),
                ]);
            })();

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
                                base_url: '/assets/js/tinymce',
                                suffix: '.min',
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
                        })
                        ();
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
