<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Notify;
use App\Services\Outbox;
use App\Services\PolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ClaimController extends Controller
{
    public function show(string $token)
    {
        $property = User::where('claim_token', $token)->whereNull('claimed_at')->firstOrFail();
        return view('auth.claim', [
            'property' => $property,
            'token'    => $token,
            'policies' => config('rmc.policies'),
        ]);
    }

    public function store(Request $request, string $token, PolicyService $policies)
    {
        $property = User::where('claim_token', $token)->whereNull('claimed_at')->firstOrFail();

        $data = $request->validate([
            'password'         => ['required', 'confirmed', Password::min(8)],
            'accept_privacy'   => ['accepted'],
            'accept_terms'     => ['accepted'],
            'accept_authority' => ['accepted'],
        ], [
            'accept_privacy.accepted'   => 'Please accept the Privacy & Data Protection Policy.',
            'accept_terms.accepted'     => 'Please accept the Terms of Membership.',
            'accept_authority.accepted' => 'Please accept the Member Authority.',
        ]);

        $property->update([
            'password'    => $data['password'],
            'claimed_at'  => now(),
            'claim_token' => null,
        ]);

        $policies->generateForUser($property);
        Outbox::welcome($property);
        Notify::admin('property_claimed', ($property->motel ?: $property->name) . ' activated their account', null, $property);

        Auth::login($property);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Welcome — your account is active. Complete your details next.');
    }
}
