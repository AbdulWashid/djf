<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.frontend.main')] class extends Component {
    public string $password = '';
    public string $type = '';

    public function mount(): void
    {
        $this->type = request()->routeIs('employer.*') ? 'employer' : 'candidate';
    }

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::guard($this->type)->user();

        if (
            !Auth::guard($this->type)->validate([
                'email' => $user->email,
                'password' => $this->password,
            ])
        ) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session([
            'auth.password_confirmed_at' => time(),
        ]);

        $this->redirectIntended(default: route($this->type . '.profile'), navigate: false);
    }
};
?>

<div class="bg-gray-50 py-16 min-h-screen flex items-center">
    <div class="container mx-auto px-4">
        <div class="row items-center">

            {{-- Left Security Content --}}
            <div class="col-lg-6 mb-5 mb-lg-0">

                <span
                    class="inline-flex items-center px-4 py-2 mb-4 text-sm font-medium rounded-full
                    {{ $type === 'employer' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">

                    Security Verification

                </span>

                <h1 class="text-4xl font-bold text-primary-800 mb-4">
                    Confirm Your Password
                </h1>

                <p class="text-lg text-gray-600 mb-8">

                    {{ $type === 'employer'
                        ? 'Before accessing sensitive employer features, please verify your identity.'
                        : 'Before accessing sensitive account settings, please verify your identity.' }}

                </p>

                <div class="space-y-6">

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">🔒</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Enhanced Security
                            </h3>
                            <p class="text-gray-600">
                                Password confirmation protects your account from unauthorized actions.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">🛡️</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Identity Verification
                            </h3>
                            <p class="text-gray-600">
                                Confirming your password helps verify that it's really you.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">⚡</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Quick Confirmation
                            </h3>
                            <p class="text-gray-600">
                                Simply enter your password to continue securely.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Confirmation Card --}}
            <div class="col-lg-6 mt-4">

                <div class="bg-white border border-gray-100 rounded-xl shadow-lg p-8">

                    <div class="text-center mb-6">

                        <div
                            class="w-20 h-20 mx-auto mb-4 rounded-full bg-primary-100 flex items-center justify-center">

                            <svg class="w-10 h-10 text-primary-800" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />

                            </svg>

                        </div>

                        <h2 class="text-2xl font-bold text-primary-800">
                            Security Check
                        </h2>

                        <p class="text-gray-500 mt-2">
                            Please confirm your password to continue.
                        </p>

                    </div>

                    <form wire:submit="confirmPassword">

                        <div class="mb-6">

                            <label class="block mb-2 text-sm font-medium">
                                Current Password
                            </label>

                            <input wire:model="password" type="password" autocomplete="current-password"
                                placeholder="Enter your password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md
                           focus:ring-1 focus:ring-primary-600
                           focus:border-primary-600">

                            @error('password')
                                <span class="text-sm text-red-500">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                        <button type="submit"
                            class="w-full py-3 text-white font-medium
                       bg-primary-800 hover:bg-primary-700
                       rounded-md transition">

                            Confirm Password

                        </button>

                    </form>

                    <div class="mt-6 text-center text-sm text-gray-500">

                        Your password is required before performing
                        sensitive account actions.

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
