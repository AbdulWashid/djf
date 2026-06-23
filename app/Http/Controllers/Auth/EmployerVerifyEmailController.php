<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employer;
use Illuminate\Auth\Events\Verified;

class EmployerVerifyEmailController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Employer::findOrFail($request->route('id'));

        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            (string) $request->route('hash')
        )) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return redirect()->route('employer.login')
            ->with(
                'success',
                'Email verified successfully. Your account is now awaiting administrator approval.'
            );
    }
}
