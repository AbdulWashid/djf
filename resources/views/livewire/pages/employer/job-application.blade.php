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
                        $qc->where('name', 'like', $searchTerm)->orWhere('email', 'like', $searchTerm);
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
            'jobsList' => \App\Models\Opening::where('employer_id', auth('employer')->id())
                ->orderBy('title')
                ->get(),
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

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <livewire:pages.employer.components.sidebar />
            </div>

            {{-- Content --}}
            <div class="lg:col-span-3">
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 sm:p-8">
                    {{-- Title Section --}}
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-5 mb-6 gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                Job Applications
                            </h2>
                            <p class="text-gray-500 text-sm mt-1">
                                Manage candidates and update their application status.
                            </p>
                        </div>
                    </div>

                    {{-- Alert Messages --}}
                    @if (session('success'))
                        <div
                            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-3">
                            <i class="fi-rr-checkbox text-lg"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-center gap-3">
                            <i class="fi-rr-exclamation text-lg"></i>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Filters Form Section --}}
                    <div class="bg-gray-50/60 p-5 rounded-xl mb-6 border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-5">
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Search
                                    Candidates</label>
                                <input type="text" wire:model.live.debounce.400ms="search"
                                    placeholder="Name, email, phone..."
                                    class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm text-sm transition">
                            </div>
                            <div class="md:col-span-3">
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Filter
                                    by Status</label>
                                <select wire:model.live="statusFilter"
                                    class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm text-sm transition">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="shortlisted">Shortlisted</option>
                                    <option value="hired">Hired</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="md:col-span-4">
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Filter
                                    by Job Posting</label>
                                <select wire:model.live="jobFilter"
                                    class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary-600 focus:border-primary-600 shadow-sm text-sm transition">
                                    <option value="">All Jobs</option>
                                    @foreach ($jobsList as $job)
                                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Table Section --}}
                    <div class="overflow-x-auto border border-gray-100 rounded-xl" wire:loading.class="opacity-60"
                        wire:target="search, statusFilter, jobFilter, updateStatus">
                        <table class="w-full text-left border-collapse" style="min-width: 800px;">
                            <thead>
                                <tr
                                    class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="py-4 px-6" style="width: 25%;">Candidate</th>
                                    <th class="py-4 px-4" style="width: 25%;">Applied Position</th>
                                    <th class="py-4 px-4" style="width: 20%;">Applied On</th>
                                    <th class="py-4 px-4" style="width: 15%;">Status</th>
                                    <th class="py-4 px-6 text-end" style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                                @forelse($applications as $application)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-4 px-6">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 text-base">
                                                    {{ $application->first_name ? $application->first_name . ' ' . $application->last_name : $application->candidate?->name ?? 'Candidate' }}
                                                </span>
                                                <span class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                                    <i class="fi-rr-envelope text-[10px]"></i>
                                                    {{ $application->email ?? $application->candidate?->email }}
                                                </span>
                                                @if ($application->phone || $application->candidate?->phone)
                                                    <span
                                                        class="text-xs text-gray-500 mt-0.5 flex items-center gap-1.5">
                                                        <i class="fi-rr-phone text-[10px]"></i>
                                                        {{ $application->phone ?? $application->candidate?->phone }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="font-semibold text-primary-800">
                                                {{ $application->opening?->title ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-gray-500">
                                            {{ $application->created_at->format('d M Y') }}
                                        </td>
                                        <td class="py-4 px-4">
                                            {{-- AlpineJS powered Dropdown --}}
                                            <div x-data="{ open: false }" class="relative inline-block text-left"
                                                @click.away="open = false">
                                                <button @click="open = !open" type="button"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-sm transition select-none cursor-pointer focus:outline-none"
                                                    style="
                                                            @if ($application->status === 'pending') background-color: #fef3c7; color: #d97706; border-color: #fde68a;
                                                            @elseif($application->status === 'reviewed') background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd;
                                                            @elseif($application->status === 'shortlisted') background-color: #f3e8ff; color: #7c3aed; border-color: #e9d5ff;
                                                            @elseif($application->status === 'hired') background-color: #d1fae5; color: #059669; border-color: #a7f3d0;
                                                            @elseif($application->status === 'rejected') background-color: #fee2e2; color: #dc2626; border-color: #fca5a5;
                                                            @else background-color: #f3f4f6; color: #4b5563; border-color: #e5e7eb; @endif
                                                        ">
                                                    <span>{{ ucfirst($application->status ?: 'pending') }}</span>
                                                    <svg class="w-3 h-3 transition duration-200"
                                                        :class="{ 'transform rotate-180': open }" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <div x-show="open" x-cloak
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="transform opacity-0 scale-95"
                                                    x-transition:enter-end="transform opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="transform opacity-100 scale-100"
                                                    x-transition:leave-end="transform opacity-0 scale-95"
                                                    class="absolute left-0 mt-1.5 w-36 rounded-lg bg-white shadow-lg border border-gray-100 py-1.5 z-10 origin-top-left focus:outline-none">
                                                    @foreach (['pending', 'reviewed', 'shortlisted', 'hired', 'rejected'] as $st)
                                                        <button type="button"
                                                            wire:click="updateStatus({{ $application->id }}, '{{ $st }}')"
                                                            @click="open = false"
                                                            class="w-full text-left px-4 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition">
                                                            {{ ucfirst($st) }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-end">
                                            @if ($application->resume_path)
                                                <a href="{{ Storage::url($application->resume_path) }}" target="_blank"
                                                    download="Candidate-CV-{{ $application->candidate_id ?? 'file' }}.{{ pathinfo($application->resume_path, PATHINFO_EXTENSION) }}"
                                                    class="inline-flex items-center px-3 py-1.5 border border-primary-100 hover:border-primary-200 text-xs font-bold rounded-lg text-primary-700 bg-primary-50 hover:bg-primary-100 transition shadow-sm">
                                                    <i class="fi-rr-download mr-1"></i> Download CV
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400 font-semibold italic">No CV
                                                    uploaded</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 px-6 text-center text-gray-500">
                                            <div
                                                class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center text-2xl mb-4 border border-gray-100 mx-auto">
                                                <i class="fi-rr-box-open"></i>
                                            </div>
                                            <h4 class="font-bold text-gray-800 text-base">
                                                No job applications found matching your criteria.
                                            </h4>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Section --}}
                    @if ($applications->hasPages())
                        <div class="mt-4 border-t border-gray-100 pt-4">
                            {{ $applications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
