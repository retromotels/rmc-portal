<?php

namespace App\Http\Controllers;

use App\Models\VetCheck;
use App\Services\InstagramLookup;
use App\Services\VettingEngine;
use Illuminate\Http\Request;

/**
 * The Vetting Desk — a member checks an Instagram creator's fit against their
 * property. Assisted mode scores the entered public numbers; the engine is
 * pluggable so a live Instagram fetch can be added later without changing this.
 */
class VettingController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.vetting'), 404);
    }

    public function index()
    {
        $this->guard();
        $prop = $this->currentProperty();

        return view('tools.vetting.index', [
            'prop'    => $prop,
            'history' => VetCheck::whereIn('property_id', $this->accountPropertyIds())
                ->latest()->limit(20)->get(),
        ]);
    }

    /** AJAX: auto-fetch a creator's public numbers to pre-fill the form. */
    public function lookup(Request $r, InstagramLookup $ig)
    {
        $this->guard();
        $r->validate(['handle' => ['required', 'string', 'max:120']]);

        return response()->json($ig->lookup($r->input('handle')));
    }

    public function run(Request $r, VettingEngine $engine)
    {
        $this->guard();

        $data = $r->validate([
            'handle'         => ['required', 'string', 'max:120'],
            'followers'      => ['required', 'integer', 'min:0', 'max:1000000000'],
            'following'      => ['nullable', 'integer', 'min:0'],
            'posts'          => ['nullable', 'integer', 'min:0'],
            'avg_likes'      => ['required', 'integer', 'min:0'],
            'avg_comments'   => ['nullable', 'integer', 'min:0'],
            'posts_per_week' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'based_location' => ['nullable', 'string', 'max:120'],
            'account_type'   => ['nullable', 'string', 'max:40'],
            'post_locations' => ['nullable', 'string', 'max:4000'],
            'captions'       => ['nullable', 'string', 'max:6000'],
            'drive_market'   => ['nullable', 'string', 'max:1000'],
            'guest_type'     => ['nullable', 'string', 'max:1000'],
        ]);

        $prop = $this->currentProperty();

        // Remember the property's vetting profile for next time.
        $prop->update([
            'ig_handle'    => $r->input('own_handle') ?: $prop->ig_handle,
            'drive_market' => $data['drive_market'] ?? $prop->drive_market,
            'guest_type'   => $data['guest_type'] ?? $prop->guest_type,
        ]);

        $locations = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $data['post_locations'] ?? ''))));

        $result = $engine->analyse([
            'handle'         => ltrim($data['handle'], '@'),
            'followers'      => $data['followers'],
            'avg_likes'      => $data['avg_likes'],
            'avg_comments'   => $data['avg_comments'] ?? 0,
            'posts_per_week' => $data['posts_per_week'] ?? 0,
            'post_locations' => $locations,
            'captions'       => $data['captions'] ?? '',
            'drive_market'   => $data['drive_market'] ?? $prop->drive_market ?? '',
            'guest_type'     => $data['guest_type'] ?? $prop->guest_type ?? '',
            'property_name'  => $prop->motel ?: $prop->name,
        ]);

        $check = VetCheck::create([
            'user_id'         => auth()->id(),
            'property_id'     => $prop->id,
            'property_name'   => $prop->motel ?: $prop->name,
            'handle'          => ltrim($data['handle'], '@'),
            'followers'       => $data['followers'],
            'following'       => $data['following'] ?? null,
            'posts'           => $data['posts'] ?? null,
            'avg_likes'       => $data['avg_likes'],
            'avg_comments'    => $data['avg_comments'] ?? null,
            'posts_per_week'  => $data['posts_per_week'] ?? null,
            'based_location'  => $data['based_location'] ?? null,
            'account_type'    => $data['account_type'] ?? null,
            'engagement_rate' => $result['engagement_rate'],
            'score'           => $result['score'],
            'verdict_tag'     => $result['verdict_tag'],
            'verdict_heading' => $result['verdict_heading'],
            'verdict_body'    => $result['verdict_body'],
            'dimensions'      => $result['dimensions'],
            'suggested_reply' => $result['suggested_reply'],
            'raw_input'       => ['locations' => $locations, 'metrics' => $result['metrics'], 'geo' => $result['geo']],
            'provider'        => config('rmc.vetting.provider'),
        ]);

        return redirect()->route('tools.vetting.result', $check);
    }

    public function result(VetCheck $vetCheck)
    {
        $this->guard();
        abort_unless($this->accountPropertyIds()->contains($vetCheck->property_id), 403);

        return view('tools.vetting.result', [
            'check'   => $vetCheck,
            'metrics' => $vetCheck->raw_input['metrics'] ?? [],
            'geo'     => $vetCheck->raw_input['geo'] ?? [],
        ]);
    }
}
