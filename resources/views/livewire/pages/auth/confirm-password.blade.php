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

        $this->redirectIntended(default: route($this->type . '.dashboard'), navigate: true);
    }
};
?>

<div class="min-h-screen bg-gray-100">
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form wire:submit="confirmPassword">
        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password"
                required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</div>
