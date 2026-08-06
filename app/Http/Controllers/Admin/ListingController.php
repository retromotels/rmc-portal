<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingAudit;
use App\Models\User;
use App\Services\BookingAnalyzer;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index()
    {
        return view('admin.listings.index', [
            'audits' => ListingAudit::with('user')->orderByDesc('updated_at')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.listings.create', [
            'motels' => User::where('role', 'owner')->orderBy('motel')->get(),
        ]);
    }

    public function store(Request $r, BookingAnalyzer $analyzer)
    {
        $data = $r->validate([
            'url'     => 'required|url|max:1000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $res = $analyzer->analyze($data['url']);

        $checks = [];
        foreach ($res['ticks'] as $key => $on) {
            if ($on) $checks[$key] = ['status' => 'ok', 'note' => 'Auto-detected from listing'];
        }

        $audit = new ListingAudit([
            'user_id'       => $data['user_id'] ?? null,
            'platform'      => 'booking',
            'url'           => $data['url'],
            'property_name' => $res['pulled']['name'] ?? null,
            'pulled'        => $res['pulled'],
            'checks'        => $checks,
        ]);
        $audit->recomputeScore();
        $audit->save();

        $msg = $res['pulled']['ok']
            ? 'Analyzed the listing and pre-ticked ' . count($checks) . ' item(s). Work through the rest below.'
            : ($res['pulled']['blocked']
                ? 'Booking.com blocked the automated read — no problem, the full checklist is ready for you to complete manually.'
                : 'Could not read the listing (' . ($res['pulled']['error'] ?: 'no content') . '). Complete the checklist manually.');

        return redirect()->route('admin.listings.show', $audit)->with('status', $msg);
    }

    public function show(ListingAudit $listing)
    {
        return view('admin.listings.show', ['audit' => $listing]);
    }

    public function update(Request $r, ListingAudit $listing)
    {
        $statuses = (array) $r->input('status', []);
        $notes    = (array) $r->input('note', []);

        $checks = [];
        foreach (array_keys(ListingAudit::allItems()) as $key) {
            $status = $statuses[$key] ?? null;
            $note   = trim((string) ($notes[$key] ?? ''));
            if ($status || $note !== '') {
                $checks[$key] = ['status' => $status ?: null, 'note' => $note];
            }
        }

        $listing->checks = $checks;
        if ($r->filled('property_name')) $listing->property_name = $r->input('property_name');
        $listing->recomputeScore();
        $listing->save();

        return redirect()->route('admin.listings.show', $listing)->with('status', 'Saved — score updated to ' . $listing->score . '%.');
    }

    public function reanalyze(ListingAudit $listing, BookingAnalyzer $analyzer)
    {
        $res = $analyzer->analyze($listing->url);
        $checks = $listing->checks ?? [];
        foreach ($res['ticks'] as $key => $on) {
            if ($on && empty($checks[$key]['status'])) {
                $checks[$key] = ['status' => 'ok', 'note' => 'Auto-detected from listing'];
            }
        }
        $listing->pulled = $res['pulled'];
        $listing->checks = $checks;
        $listing->recomputeScore();
        $listing->save();

        return back()->with('status', $res['pulled']['ok'] ? 'Re-analyzed the listing.' : 'Re-analyze could not read the listing.');
    }

    public function destroy(ListingAudit $listing)
    {
        $listing->delete();
        return redirect()->route('admin.listings.index')->with('status', 'Audit deleted.');
    }
}
