<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $redirect = route('home');

        if (Auth::guard('employer')->check()) {
            Auth::guard('employer')->logout();
            $redirect = route('employer.login');
        } elseif (Auth::guard('candidate')->check()) {
            Auth::guard('candidate')->logout();
            $redirect = route('candidate.login');
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($redirect);
    }
}
