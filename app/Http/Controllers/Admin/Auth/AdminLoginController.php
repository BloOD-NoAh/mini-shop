<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (! optional(Auth::user())->is_admin) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'You do not have admin access.',
            ]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}

