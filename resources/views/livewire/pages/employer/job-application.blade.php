<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\JobApplications;

new #[Layout('components.frontend.main')] class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $jobFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'jobFilter' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedJobFilter()
    {
        $this->resetPage();
    }

    public function updateStatus($id, $newStatus)
    {
        $application = JobApplications::whereHas('opening', function ($query) {
            $query->where('employer_id', auth('employer')->id());
        })->findOrFail($id);

        if (!in_array($newStatus, ['pending', 'reviewed', 'shortlisted', 'rejected', 'hired'])) {
            session()->flash('error', 'Invalid status selected.');
            return;
        }

        $application->update(['status' => $newStatus]);
        session()->flash('success', 'Application status updated to ' . ucfirst($newStatus) . ' successfully.');
    }

    public function with(): array
    {
        $query = JobApplications::query()
            ->with(['candidate', 'opening.employer'])
            ->whereHas('opening', function ($q) {
                $q->where('employer_id', auth('employer')->id());
            });

        if (trim($this->search) !== '') {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', $searchTerm)
                  ->orWhere('last_name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('phone', 'like', $searchTerm)
                  ->orWhereHas('candidate', function ($qc) use ($searchTerm) {
                      $qc->where('name', 'like', $searchTerm)
                         ->orWhere('email', 'like', $searchTerm);
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->jobFilter) {
            $query->where('opening_id', $this->jobFilter);
        }

        return [
            'applications' => $query->latest()->paginate(10),
            'jobsList' => \App\Models\Opening::where('employer_id', auth('employer')->id())->orderBy('title')->get(),
        ];
    }
}; ?>

<div>
    <div class="breacrumb-cover">
        <div class="container">
            <ul class="breadcrumbs">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li>Employer</li>
                <li>Applications</li>
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
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-6">
                        
                        {{-- Title Section --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap border-bottom pb-4 mb-4 gap-3">
                            <div>
                                <h2 class="text-2xl font-bold text-primary-800 mb-1">
                                    Job Applications
                                </h2>
                                <p class="text-gray-600 mb-0">
                                    Manage candidates and update their application status.
                                </p>
                            </div>
                        </div>

                        {{-- Alert Messages --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                                <i class="fi-rr-checkbox me-2 text-xl"></i>
                                <div>{{ session('success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
                                <i class="fi-rr-exclamation me-2 text-xl"></i>
                                <div>{{ session('error') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Filters Form Section --}}
                        <div class="bg-light p-4 rounded-lg mb-4 border border-gray-100">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label font-semibold text-gray-700 mb-1">Search Candidates</label>
                                    <div class="position-relative">
                                        <input type="text" 
                                               wire:model.live.debounce.400ms="search" 
                                               placeholder="Name, email, phone..." 
                                               class="form-control ps-4" 
                                               style="height: 42px; border-radius: 6px; border: 1px solid #d1d5db; focus: border-color: #572b8d; focus: ring: 2px #d2bcf6;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label font-semibold text-gray-700 mb-1">Filter by Status</label>
                                    <select wire:model.live="statusFilter" 
                                            class="form-control" 
                                            style="height: 42px; border-radius: 6px; border: 1px solid #d1d5db;">
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="reviewed">Reviewed</option>
                                        <option value="shortlisted">Shortlisted</option>
                                        <option value="hired">Hired</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-semibold text-gray-700 mb-1">Filter by Job Posting</label>
                                    <select wire:model.live="jobFilter" 
                                            class="form-control" 
                                            style="height: 42px; border-radius: 6px; border: 1px solid #d1d5db;">
                                        <option value="">All Jobs</option>
                                        @foreach($jobsList as $job)
                                            <option value="{{ $job->id }}">{{ $job->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Table Section --}}
                        <div class="table-responsive" wire:loading.class="opacity-60" wire:target="search, statusFilter, jobFilter, updateStatus">
                            <table class="table align-middle" style="min-width: 800px;">
                                <thead>
                                    <tr class="bg-gray-50 border-bottom border-gray-100">
                                        <th class="py-3 px-4 text-gray-700 font-semibold" style="width: 25%;">Candidate</th>
                                        <th class="py-3 px-3 text-gray-700 font-semibold" style="width: 25%;">Applied Position</th>
                                        <th class="py-3 px-3 text-gray-700 font-semibold" style="width: 20%;">Applied On</th>
                                        <th class="py-3 px-3 text-gray-700 font-semibold" style="width: 15%;">Status</th>
                                        <th class="py-3 px-4 text-gray-700 font-semibold text-end" style="width: 15%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @forelse($applications as $application)
                                        <tr class="hover:bg-gray-50/50 border-bottom border-gray-100 transition duration-150">
                                            <td class="py-3.5 px-4">
                                                <div class="d-flex flex-column">
                                                    <span class="font-bold text-gray-800 text-base">
                                                        {{ $application->first_name ? ($application->first_name . ' ' . $application->last_name) : ($application->candidate?->name ?? 'Candidate') }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 mt-0.5 d-flex align-items-center gap-1">
                                                        <i class="fi-rr-envelope text-[10px]"></i> {{ $application->email ?? $application->candidate?->email }}
                                                    </span>
                                                    @if($application->phone || $application->candidate?->phone)
                                                        <span class="text-xs text-gray-500 mt-0.5 d-flex align-items-center gap-1">
                                                            <i class="fi-rr-phone text-[10px]"></i> {{ $application->phone ?? $application->candidate?->phone }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-3">
                                                <span class="font-semibold text-primary-800">
                                                    {{ $application->opening?->title ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-3 text-gray-600 text-sm">
                                                {{ $application->created_at->format('d M Y') }}
                                            </td>
                                            <td class="py-3.5 px-3">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm dropdown-toggle text-xs font-bold py-1 px-3 rounded-full border-0 d-inline-flex align-items-center gap-1 shadow-sm" 
                                                            type="button" 
                                                            data-bs-toggle="dropdown" 
                                                            aria-expanded="false"
                                                            style="
                                                                @if($application->status === 'pending') background-color: #fef3c7; color: #d97706;
                                                                @elseif($application->status === 'reviewed') background-color: #e0f2fe; color: #0284c7;
                                                                @elseif($application->status === 'shortlisted') background-color: #f3e8ff; color: #7c3aed;
                                                                @elseif($application->status === 'hired') background-color: #d1fae5; color: #059669;
                                                                @elseif($application->status === 'rejected') background-color: #fee2e2; color: #dc2626;
                                                                @else background-color: #f3f4f6; color: #4b5563;
                                                                @endif
                                                            ">
                                                        {{ ucfirst($application->status ?: 'pending') }}
                                                    </button>
                                                    <ul class="dropdown-menu shadow border-0 py-2 mt-1">
                                                        <li><button class="dropdown-item py-1.5 px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50" type="button" wire:click="updateStatus({{ $application->id }}, 'pending')">Pending</button></li>
                                                        <li><button class="dropdown-item py-1.5 px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50" type="button" wire:click="updateStatus({{ $application->id }}, 'reviewed')">Reviewed</button></li>
                                                        <li><button class="dropdown-item py-1.5 px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50" type="button" wire:click="updateStatus({{ $application->id }}, 'shortlisted')">Shortlisted</button></li>
                                                        <li><button class="dropdown-item py-1.5 px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50" type="button" wire:click="updateStatus({{ $application->id }}, 'hired')">Hired</button></li>
                                                        <li><button class="dropdown-item py-1.5 px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50" type="button" wire:click="updateStatus({{ $application->id }}, 'rejected')">Rejected</button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-4 text-end">
                                                @if ($application->resume_path)
                                                    <a href="{{ Storage::url($application->resume_path) }}"
                                                       target="_blank"
                                                       download="Candidate-CV-{{ $application->candidate_id ?? 'file' }}.{{ pathinfo($application->resume_path, PATHINFO_EXTENSION) }}"
                                                       class="btn btn-sm btn-outline-primary border-primary-800 text-primary-800 hover:bg-primary-800 hover:text-white d-inline-flex align-items-center gap-1 font-semibold py-1 px-3 rounded shadow-sm">
                                                        <i class="fi-rr-download text-xs"></i> Download CV
                                                    </a>
                                                @else
                                                    <span class="text-xs text-gray-400 font-semibold italic">No CV uploaded</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-gray-500">
                                                <i class="fi-rr-box-open text-3xl mb-2 d-block opacity-40"></i>
                                                No job applications found matching your criteria.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Section --}}
                        <div class="mt-4 border-top pt-4">
                            {{ $applications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
