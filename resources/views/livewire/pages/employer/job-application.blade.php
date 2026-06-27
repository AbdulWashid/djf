<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\JobApplications;

new #[Layout('components.frontend.main')] class extends Component {
    use WithPagination;
    public function with(): array
    {
        // dd(
        //     JobApplications::query()
        //         ->with(['candidate', 'opening.employer'])
        //         ->whereHas('opening', function ($query) {
        //             $query->where('employer_id', auth('employer')->id());
        //         })
        //         ->latest()
        //         ->paginate(10),
        // );
        return [
            'applications' => JobApplications::query()
                ->with(['candidate', 'opening.employer'])
                ->whereHas('opening', function ($query) {
                    $query->where('employer_id', auth('employer')->id());
                })
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

                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-primary-800">
                                Job Applications
                            </h2>

                            <p class="text-gray-600">
                                View applications submitted for your jobs.
                            </p>
                        </div>

                        <div class="table-responsive">

                            <table class="table">

                                <thead>
                                    <tr>
                                        <th>Candidate</th>
                                        <th>Job Title</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Applied On</th>
                                        <th width="150">Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($applications as $application)
                                        <tr>

                                            <td>
                                                {{ $application->candidate?->name }}
                                            </td>

                                            <td>
                                                {{ $application->opening?->title }}
                                            </td>

                                            <td>
                                                {{ $application->candidate?->email }}
                                            </td>

                                            <td>
                                                {{ $application->candidate?->phone }}
                                            </td>

                                            <td>
                                                {{ $application->created_at->format('d M Y') }}
                                            </td>

                                            <td>

                                                @if ($application->resume_path)
                                                    <a href="{{ Storage::url($application->resume_path) }}"
                                                        target="_blank" class="text-primary-600">

                                                        View CV

                                                    </a>
                                                @endif

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                No applications found.
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
