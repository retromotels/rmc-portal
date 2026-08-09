<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Outbox;
use App\Services\PolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email or password is incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isAdmin() ? route('admin.overview') : route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request, PolicyService $policies)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'motel'    => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'accept_privacy'   => ['accepted'],
            'accept_terms'     => ['accepted'],
            'accept_authority' => ['accepted'],
        ], [
            'accept_privacy.accepted'   => 'Please accept the Privacy & Data Protection Policy.',
            'accept_terms.accepted'     => 'Please accept the Terms of Membership.',
            'accept_authority.accepted' => 'Please accept the Member Authority.',
        ]);

        $user = User::create([
            'name'             => $data['name'],
            'motel'            => $data['motel'],
            'email'            => $data['email'],
            'password'         => $data['password'],
            'role'             => 'owner',
            'band'             => 'small',
            'tier'             => 'standard',
            'details_complete' => false,
            'founding'         => (bool) config('rmc.founding.active'),
        ]);

        // Generate + store the three digitally-signed policy PDFs.
        $policies->generateForUser($user);

        // Queue the welcome email + notify head office (recorded in the outbox).
        Outbox::welcome($user);
        Outbox::adminNewSignup($user);

        Auth::login($user);
        $request->session()->regenerate();

        // Send them into the portal; the dashboard prompts them to finish details.
        return redirect()->route('details.show')->with('status', 'Welcome — let’s complete your details.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
