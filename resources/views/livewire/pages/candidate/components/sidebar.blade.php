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

<div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6">
    <div class="flex flex-col items-center text-center">
        @if ($candidate->logo)
            <img loading="lazy" src="{{ Storage::url($candidate->logo) }}"
                class="rounded-full shadow-inner border border-gray-100" style="width:96px;height:96px;object-fit:cover;">
        @else
            <div
                class="w-24 h-24 rounded-full bg-primary-50 text-primary-700 flex items-center justify-center font-bold text-2xl border border-primary-100 shadow-sm mb-3">
                {{ substr($candidate->name, 0, 1) }}
            </div>
        @endif

        <h4 class="text-gray-800 font-bold text-lg leading-snug">
            {{ $candidate->name }}
        </h4>

        <p class="text-gray-500 text-sm mt-1">
            {{ $candidate->email }}
        </p>
    </div>

    <div class="h-px bg-gray-100 my-6"></div>

    <nav class="space-y-1">
        <a href="{{ route('candidate.profile') }}"
            class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition duration-200 @if (route('candidate.profile') == request()->url()) bg-primary-50 text-primary-700 @else text-gray-600 hover:bg-gray-50 hover:text-gray-900 @endif">
            <i class="fi-rr-user mr-3 text-base"></i>
            My Profile
        </a>

        <a href="{{ route('candidate.applied') }}"
            class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition duration-200 @if (route('candidate.applied') == request()->url()) bg-primary-50 text-primary-700 @else text-gray-600 hover:bg-gray-50 hover:text-gray-900 @endif">
            <i class="fi-rr-briefcase mr-3 text-base"></i>
            Applied Jobs
        </a>

        <button wire:click="logout"
            class="w-full flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition duration-200 mt-2">
            <i class="fi-rr-sign-out mr-3 text-base"></i>
            Logout
        </button>
    </nav>
</div>
