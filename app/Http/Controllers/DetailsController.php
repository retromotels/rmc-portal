<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

/**
 * The "complete your details" flow (sections A & B + tier), shown right after
 * sign-up. Presented as a single guided page; auto-selects tier from rooms.
 */
class DetailsController extends Controller
{
    public function show()
    {
        return view('details', [
            'A' => config('rmc.sections.A'),
            'B' => config('rmc.sections.B'),
            'user' => auth()->user(),
        ]);
    }

    public function save(Request $request)
    {
        $user = $request->user();

        $aFields = collect(config('rmc.sections.A.fields'));
        $bFields = collect(config('rmc.sections.B.fields'));

        // Validate required non-file fields for A and B.
        $rules = [];
        foreach ($aFields->merge($bFields) as $f) {
            if (($f['req'] ?? false) && ($f['type'] ?? '') !== 'file') {
                $rules["fields.{$f['id']}"] = ['required', 'string'];
            }
        }
        $request->validate($rules);

        $input = $request->input('fields', []);
        $aData = $this->pick($input, $aFields);
        $bData = $this->pick($input, $bFields);

        Registration::updateOrCreate(['user_id' => $user->id, 'section' => 'A'], ['data' => $aData]);
        Registration::updateOrCreate(['user_id' => $user->id, 'section' => 'B'], ['data' => $bData]);

        $band = \App\Models\User::bandFromRooms($aData['totalRooms'] ?? 0);

        $user->update([
            'motel'            => $aData['propertyName'] ?? $user->motel,
            'loc'              => trim(($aData['city'] ?? '') . (isset($aData['state']) ? ', ' . $aData['state'] : ''), ', '),
            'band'             => $band,
            'tier'             => \App\Models\User::tierFromBand($band),
            'details_complete' => true,
        ]);

        return redirect()->route('dashboard')->with('status', 'Details saved — thank you!');
    }

    private function pick(array $input, $fields): array
    {
        $out = [];
        foreach ($fields as $f) {
            if (($f['type'] ?? '') === 'file') continue;
            if (array_key_exists($f['id'], $input)) {
                $out[$f['id']] = $input[$f['id']];
            }
        }
        return $out;
    }
}
