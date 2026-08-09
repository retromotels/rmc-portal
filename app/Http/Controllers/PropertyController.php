<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    /** Switch which property the portal is showing. */
    public function switch(Request $r)
    {
        $r->validate(['property_id' => 'required|integer']);
        if ($this->accountPropertyIds()->contains((int) $r->input('property_id'))) {
            session(['current_property_id' => (int) $r->input('property_id')]);
        }
        return back();
    }

    public function add()
    {
        return view('properties.add');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'motel' => ['required', 'string', 'max:255'],
            'rooms' => ['nullable', 'integer', 'min:0'],
        ]);

        $account = auth()->user();
        $band = User::bandFromRooms($data['rooms'] ?? 0);

        $property = User::create([
            'name'             => $account->name,
            'email'            => 'property-' . Str::uuid() . '@properties.retromotels.local',
            'password'         => Str::random(48),   // no login for child rows
            'role'             => 'owner',
            'account_id'       => $account->accountId(),
            'motel'            => $data['motel'],
            'band'             => $band,
            'tier'             => User::tierFromBand($band),
            'details_complete' => false,
            'founding'         => (bool) $account->founding,
        ]);

        session(['current_property_id' => $property->id]);

        return redirect()->route('details.show')
            ->with('status', 'Property added — complete its details next.');
    }
}
