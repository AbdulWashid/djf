<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public $candidate;

    public function mount()
    {
        $this->candidate = Auth::guard('candidate')->user();
    }

    public function logout()
    {
        Auth::guard('candidate')->logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('candidate.login');
    }
}; ?>

<div class="bg-white border border-gray-200 rounded-lg shadow-md p-4">
    <div class="text-center">

        @if ($candidate->logo)
            <img src="{{ Storage::url($candidate->logo) }}" class="mx-auto rounded-full mb-3"
                style="width:120px;height:120px;object-fit:cover;">
        @else
            <img src="https://placehold.co/120x120?text={{ substr($candidate->name, 0, 1) }}"
                class="mx-auto rounded-full mb-3">
        @endif

        <h4 class="text-primary-800 font-bold">
            {{ $candidate->first_name . $candidate->last_name }}
        </h4>

        <p class="text-gray-500">
            {{ $candidate->email }}
        </p>

    </div>

    <hr class="my-4">

    <ul>

        {{-- <li class="mb-3">
            <a href="{{ route('candidate.profile') }}"
                @if (route('candidate.profile') == request()->url()) class="text-primary-800 font-medium" @endif>
                My Profile
            </a>
        </li>

        <li class="mb-3">
            <a href="{{ route('candidate.job-posting') }}"
                @if (route('candidate.job-posting') == request()->url()) class="text-primary-800 font-medium" @endif>
                Job Posting
            </a>
        </li> --}}

        <li class="mb-3">
            <a href="{{ route('candidate.applied') }}"
                @if (route('candidate.applied') == request()->url()) class="text-primary-800 font-medium" @endif>
                Applied Job
            </a>
        </li>
        <li class="mb-3">
            <button wire:click="logout" class="text-red-600 font-medium">
                Logout
            </button>
        </li>

    </ul>

</div>
