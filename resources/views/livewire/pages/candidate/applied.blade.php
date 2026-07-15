<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\JobApplications;

new #[Layout('components.frontend.main')] class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'applications' => JobApplications::with(['opening.location', 'opening.employer'])
                ->where('candidate_id', Auth::guard('candidate')->id())
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
                <li>Candidate Dashboard</li>
                <li>My Applications</li>
            </ul>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                <livewire:pages.candidate.components.sidebar />
            </div>

            {{-- Content --}}
            <div class="lg:col-span-3">
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">
                                    My Applications
                                </h3>
                                <p class="text-gray-500 text-sm mt-1">
                                    Track the status of all your job applications.
                                </p>
                            </div>
                            <a href="{{ route('jobs') }}"
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-primary-800 hover:bg-primary-700 transition">
                                <i class="fi-rr-search mr-2"></i> Browse Jobs
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="py-4 px-6" style="width: 80px;">#</th>
                                    <th class="py-4 px-6">Job Details</th>
                                    <th class="py-4 px-6" style="width: 160px;">Applied</th>
                                    <th class="py-4 px-6" style="width: 140px;">Status</th>
                                    <th class="py-4 px-6" style="width: 250px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                                @forelse($applications as $application)
                                    @php
                                        $badgeClasses = match ($application->status) {
                                            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'reviewed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'shortlisted' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'selected', 'hired' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };
                                    @endphp

                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-4 px-6 font-semibold text-gray-900">
                                            {{ ($applications->currentPage() - 1) * $applications->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-gray-900 text-base">
                                                {{ $application->opening?->title }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 mt-1">
                                                @if ($application->opening?->company_name)
                                                    <span class="flex items-center">
                                                        <i class="fi-rr-building mr-1"></i>
                                                        {{ $application->opening->company_name }}
                                                    </span>
                                                @endif
                                                <span class="flex items-center">
                                                    <i class="fi-rr-marker mr-1"></i>
                                                    {{ $application->opening?->location?->name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="font-semibold text-gray-800">
                                                {{ $application->created_at->format('d M Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                {{ $application->created_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                                                {{ ucfirst($application->status ?: 'Pending') }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ Storage::url($application->resume_path) }}" target="_blank"
                                                    class="inline-flex items-center px-3 py-1.5 border border-gray-200 hover:border-gray-300 text-xs font-bold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                                                    <i class="fi-rr-download mr-1"></i>
                                                    Resume
                                                </a>
                                                <a href="{{ route('jobs.show', $application->opening->slug) }}"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-lg text-white bg-primary-800 hover:bg-primary-700 transition shadow-sm"
                                                    target="_blank">
                                                    <i class="fi-rr-eye mr-1"></i>
                                                    View
                                                </a>
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
                                                    <i class="fi-rr-briefcase"></i>
                                                </div>
                                                <h4 class="font-bold text-gray-800 text-lg">
                                                    No Applications Yet
                                                </h4>
                                                <p class="text-gray-500 text-sm mt-1 mb-6">
                                                    You haven't applied for any jobs. Start exploring opportunities now.
                                                </p>
                                                <a href="{{ route('jobs') }}"
                                                    class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-lg shadow-md text-white bg-primary-800 hover:bg-primary-700 transition">
                                                    <i class="fi-rr-search mr-2"></i> Browse Jobs
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($applications->hasPages())
                        <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                            {{ $applications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
