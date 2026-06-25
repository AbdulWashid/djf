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
            'applications' => JobApplications::with('opening')
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

                    <div class="bg-white border rounded shadow-sm p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h3 class="mb-1">My Applications</h3>
                                <p class="text-muted mb-0">
                                    Track all jobs you've applied for.
                                </p>
                            </div>
                        </div>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Job</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                        <th>Resume</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($applications as $application)
                                        <tr>

                                            <td>{{ $application->id }}</td>

                                            <td>
                                                <strong>
                                                    {{ $application->opening?->title }}
                                                </strong>

                                                <div class="small text-muted">
                                                    {{ $application->opening?->location }}
                                                </div>
                                            </td>

                                            <td>
                                                {{ $application->created_at->format('d M Y') }}
                                            </td>

                                            <td>

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

                                                <span class="badge bg-{{ $badge }}">
                                                    {{ ucfirst($application->status) }}
                                                </span>

                                            </td>

                                            <td>

                                                <a href="{{ Storage::url($application->resume_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary">

                                                    Download

                                                </a>

                                            </td>

                                            <td>

                                                <a href="{{ route('jobs.show', $application->opening->slug) }}"
                                                    class="btn btn-sm btn-primary">

                                                    View Job

                                                </a>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <h5>No applications found.</h5>
                                                <p class="text-muted">
                                                    You haven't applied for any jobs yet.
                                                </p>

                                                <a href="{{ route('jobs.index') }}" class="btn btn-primary">

                                                    Browse Jobs

                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                        <div class="mt-4">
                            {{ $applications->links() }}
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>
</div>
