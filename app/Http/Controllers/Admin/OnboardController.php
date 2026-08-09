<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnboardController extends Controller
{
    public function create()
    {
        return view('admin.onboard.create', [
            'pending' => User::whereNotNull('claim_token')->whereNull('claimed_at')
                ->where('created_by_admin', true)->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'motel' => ['required', 'string', 'max:255'],
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'rooms' => ['nullable', 'integer', 'min:0'],
        ]);

        $band = User::bandFromRooms($data['rooms'] ?? 0);

        $property = User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'password'         => Str::random(48),      // placeholder until they claim
            'role'             => 'owner',
            'motel'            => $data['motel'],
            'band'             => $band,
            'tier'             => User::tierFromBand($band),
            'details_complete' => false,
            'founding'         => (bool) config('rmc.founding.active'),
            'claim_token'      => Str::random(48),
            'created_by_admin' => true,
        ]);

        $link = route('claim.show', $property->claim_token);

        return redirect()->route('admin.onboard.create')
            ->with('status', 'Property created. Send this activation link to the operator:')
            ->with('invite_link', $link);
    }
}
