<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.frontend.main')] class extends Component {
    public string $type;

    public function mount()
    {
        $this->type = request()->routeIs('employer.*') ? 'employer' : 'candidate';
    }

    public function sendVerification(): void
    {
        $user = Auth::guard($this->type)->user();

        if (!$user) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route($this->type . '.dashboard'), navigate: true);

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
};
?>

<div class="bg-gray-50 py-16 min-h-screen flex items-center">
    <div class="container mx-auto px-4">

        <div class="row items-center">

            {{-- Left Side --}}
            <div class="col-lg-6 mb-5 mb-lg-0">

                <span
                    class="inline-flex items-center px-4 py-2 mb-4 text-sm font-medium rounded-full
                    {{ $type === 'employer' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">

                    {{ $type === 'employer' ? 'Employer Verification' : 'Candidate Verification' }}

                </span>

                <h1 class="text-4xl font-bold text-primary-800 mb-4">

                    {{ $type === 'employer' ? 'Verify Your Company Account' : 'Verify Your Email Address' }}

                </h1>

                <p class="text-lg text-gray-600 mb-8">

                    {{ $type === 'employer'
                        ? 'Verify your email address to start posting jobs and managing applicants.'
                        : 'Verify your email address to begin applying for jobs and accessing your dashboard.' }}

                </p>

                <div class="space-y-6">

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">📧</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Email Verification
                            </h3>
                            <p class="text-gray-600">
                                Confirm ownership of your email address.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">🔒</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Secure Access
                            </h3>
                            <p class="text-gray-600">
                                Protect your account against unauthorized access.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="mr-4 text-2xl">⚡</div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                Quick Activation
                            </h3>
                            <p class="text-gray-600">
                                One click is all it takes to activate your account.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Right Side --}}
            <div class="col-lg-6">

                <div class="bg-white border border-gray-100 rounded-xl shadow-lg p-8">

                    <div class="text-center mb-6">

                        <div
                            class="w-20 h-20 mx-auto mb-4 rounded-full bg-primary-100 flex items-center justify-center">

                            <svg class="w-10 h-10 text-primary-800" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v8a2 2 0 002 2z" />

                            </svg>

                        </div>

                        <h2 class="text-2xl font-bold text-primary-800">
                            Check Your Inbox
                        </h2>

                    </div>

                    <p class="text-gray-600 text-center mb-6">

                        We've sent a verification link to your email address.
                        Click the link to verify your account and continue.

                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="mb-6 p-4 rounded-lg border border-green-200 bg-green-50 text-green-700">

                            A new verification link has been sent successfully.

                        </div>
                    @endif

                    <div class="space-y-3">

                        <button wire:click="sendVerification"
                            class="w-full py-3 bg-primary-800 hover:bg-primary-700 text-white rounded-md font-medium transition">

                            Resend Verification Email

                        </button>

                        <button wire:click="logout" type="button"
                            class="w-full py-3 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-md font-medium transition">

                            Log Out

                        </button>

                    </div>

                    <div class="mt-6 text-center text-sm text-gray-500">

                        Didn't receive the email?

                        <br>

                        Check your spam folder or click
                        <strong>Resend Verification Email</strong>.

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
