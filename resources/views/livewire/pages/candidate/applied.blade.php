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

    <section class="mt-20 mb-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <livewire:pages.candidate.components.sidebar />
                </div>
                <div class="col-lg-9">
                    <div class="bg-white shadow-sm rounded border overflow-hidden">
                        <div class="p-4 border-bottom">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h3 class="mb-1">
                                        My Applications
                                    </h3>
                                    <p class="text-muted mb-0">
                                        Track the status of all your job applications.
                                    </p>
                                </div>
                                <a href="{{ route('jobs') }}" class="btn btn-default">
                                    Browse Jobs
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 px-3">
                                <thead class="table-light">
                                    <tr>
                                        <th width="70">#</th>
                                        <th>Job Details</th>
                                        <th width="150">Applied</th>
                                        <th width="140">Status</th>
                                        <th width="120">Resume</th>
                                        <th width="130">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $application)
                                        @php
                                            $badge = match ($application->status) {
                                                'pending' => 'warning',
                                                'shortlisted' => 'primary',
                                                'interview' => 'info',
                                                'selected' => 'success',
                                                'rejected' => 'danger',
                                                default => 'secondary',
                                            };
                                        @endphp

                                        <tr>
                                            <td class="fw-semibold">
                                                {{ ($applications->currentPage() - 1) * $applications->perPage() + $loop->iteration }}
                                            </td>
                                            <td>
                                                <div class="fw-semibold fs-6">
                                                    {{ $application->opening?->title }}
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    @if ($application->opening?->company_name)
                                                        <i class="bi bi-building me-1"></i>
                                                        {{ $application->opening->company_name }}
                                                        &nbsp;&nbsp;
                                                    @endif
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    {{ $application->opening?->location?->name }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $application->created_at->format('d M Y') }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ $application->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $badge }} px-3 py-2 rounded-pill">
                                                    {{ ucfirst($application->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ Storage::url($application->resume_path) }}" target="_blank"
                                                    class="btn btn-light btn-sm border">
                                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>
                                                    Resume
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('jobs.show', $application->opening->slug) }}"
                                                    class="btn btn-primary btn-sm" target="_blank">
                                                    <i class="bi bi-eye me-1"></i>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="text-center py-5">
                                                    <i class="bi bi-briefcase display-3 text-muted"></i>
                                                    <h4 class="mt-3">
                                                        No Applications Yet
                                                    </h4>
                                                    <p class="text-muted mb-4">
                                                        You haven't applied for any jobs. Start exploring opportunities
                                                        now.
                                                    </p>
                                                    <a href="{{ route('jobs') }}" class="btn btn-primary px-4">
                                                        <i class="bi bi-search me-2"></i>
                                                        Browse Jobs
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($applications->hasPages())
                            <div class="px-3">
                                {{ $applications->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
