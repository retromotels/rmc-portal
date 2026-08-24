<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class EmployerAuthController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.external_jobs'), 404);
    }

    public function showRegister()
    {
        $this->guard();
        if (Auth::guard('employer')->check()) {
            return redirect()->route('employer.dashboard');
        }
        return view('jobs.employers.register');
    }

    public function register(Request $r)
    {
        $this->guard();
        $data = $r->validate([
            'company'  => ['required', 'string', 'max:160'],
            'name'     => ['nullable', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:190', 'unique:employers,email'],
            'phone'    => ['nullable', 'string', 'max:40'],
            'website'  => ['nullable', 'string', 'max:200'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $employer = Employer::create($data);
        Auth::guard('employer')->login($employer);

        return redirect()->intended(route('employer.dashboard'));
    }

    public function showLogin()
    {
        $this->guard();
        if (Auth::guard('employer')->check()) {
            return redirect()->route('employer.dashboard');
        }
        return view('jobs.employers.login');
    }

    public function login(Request $r)
    {
        $this->guard();
        $cred = $r->validate(['email' => ['required', 'email'], 'password' => ['required']]);

        if (Auth::guard('employer')->attempt($cred, $r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect()->intended(route('employer.dashboard'));
        }

        return back()->withErrors(['email' => "Those details don't match our records."])->onlyInput('email');
    }

    public function logout(Request $r)
    {
        Auth::guard('employer')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('employers.pricing');
    }
}
