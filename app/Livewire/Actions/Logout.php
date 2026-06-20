<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        if (Auth::guard('employer')->check()) {
            Auth::guard('employer')->logout();
        }

        if (Auth::guard('candidate')->check()) {
            Auth::guard('candidate')->logout();
        }

        Session::invalidate();
        Session::regenerateToken();
    }
}
