<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SeekerAuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::guard('seeker')->check()) {
            return redirect()->route('seeker.dashboard');
        }
        return view('jobs.public.register');
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:190', 'unique:job_seekers,email'],
            'phone'    => ['nullable', 'string', 'max:40'],
            'state'    => ['nullable', Rule::in(array_keys(config('rmc.job_states')))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $seeker = JobSeeker::create($data);
        Auth::guard('seeker')->login($seeker);

        return redirect()->intended(route('seeker.dashboard'));
    }

    public function showLogin()
    {
        if (Auth::guard('seeker')->check()) {
            return redirect()->route('seeker.dashboard');
        }
        return view('jobs.public.login');
    }

    public function login(Request $r)
    {
        $cred = $r->validate(['email' => ['required', 'email'], 'password' => ['required']]);

        if (Auth::guard('seeker')->attempt($cred, $r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect()->intended(route('seeker.dashboard'));
        }

        return back()->withErrors(['email' => "Those details don't match our records."])->onlyInput('email');
    }

    public function logout(Request $r)
    {
        Auth::guard('seeker')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('jobs.board');
    }
}
