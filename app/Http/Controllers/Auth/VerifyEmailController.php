<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    
    public function __invoke(EmailVerificationRequest $request)
    {
        $request->fulfill();

        if (auth('employer')->check()) {
            return redirect()->route('employer.dashboard');
        }

        if (auth('candidate')->check()) {
            return redirect()->route('candidate.dashboard');
        }

        return redirect('/');
    }
}
