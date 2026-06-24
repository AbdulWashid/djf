<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $employer;

    public function mount()
    {
        $this->employer = Auth::guard('employer')->user();
    }
    public function logout()
    {
        Auth::guard('employer')->logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('employer.login');
    }
};
?>

<div class="bg-white border border-gray-200 rounded-lg shadow-md p-4">
    <div class="text-center">

        @if ($employer->logo)
            <img src="{{ Storage::url($employer->logo) }}" class="mx-auto rounded-full mb-3"
                style="width:120px;height:120px;object-fit:cover;">
        @else
            <img src="https://placehold.co/120x120?text={{ substr($employer->name, 0, 1) }}"
                class="mx-auto rounded-full mb-3">
        @endif

        <h4 class="text-primary-800 font-bold">
            {{ $employer->name }}
        </h4>

        <p class="text-gray-500">
            {{ $employer->email }}
        </p>

    </div>

    <hr class="my-4">

    <ul>

        <li class="mb-3">
            <a href="{{ route('employer.profile') }}"
                @if (route('employer.profile') == request()->url()) class="text-primary-800 font-medium" @endif>
                My Profile
            </a>
        </li>

        <li class="mb-3">
            <a href="{{ route('employer.job-posting') }}"
                @if (route('employer.job-posting') == request()->url()) class="text-primary-800 font-medium" @endif>
                Job Posting
            </a>
        </li>

        <li class="mb-3">
            <a href="{{ route('employer.job-application') }}"
                @if (route('employer.job-application') == request()->url()) class="text-primary-800 font-medium" @endif>
                Applications
            </a>
        </li>
        <li class="mb-3">
            <button onclick="if(confirm('Are you sure you want to logout?')) { $wire.logout() }"
                class="text-red-600 font-medium">
                Logout
            </button>
        </li>

    </ul>

</div>
