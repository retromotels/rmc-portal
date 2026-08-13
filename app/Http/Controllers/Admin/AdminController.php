<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyDocument;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private function members()
    {
        return User::where('role', 'owner')
            ->with('policyDocuments', 'registrations', 'uploads')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function overview()
    {
        $members = $this->members();
        return view('admin.overview', [
            'members' => $members,
            'pending' => $members->where('details_complete', false)->count(),
        ]);
    }

    public function motels()
    {
        return view('admin.motels', ['members' => $this->members()]);
    }

    public function motel(User $user)
    {
        abort_if($user->isAdmin(), 404);
        $user->load('policyDocuments', 'registrations', 'uploads');
        return view('admin.motel', [
            'member'   => $user,
            'sections' => collect(config('rmc.sections'))->map(fn ($s, $id) => $s + ['id' => $id]),
        ]);
    }

    /**
     * Permanently delete a motel (its profile + account). If it's a top-level
     * account, its linked child properties go too. FK cascade removes the
     * registrations, uploads, policy docs, chat widget etc.; we clear the
     * on-disk files first. Guarded by a type-the-name confirmation.
     */
    public function destroy(Request $r, User $user)
    {
        abort_if($user->isAdmin(), 403);

        $expected = trim($user->motel ?: $user->name);
        if (trim((string) $r->input('confirm')) !== $expected || $expected === '') {
            return back()->with('status', '❌ The name you typed did not match — nothing was deleted.');
        }

        // This property, plus any child properties if it's a top-level account.
        $ids = collect([$user->id]);
        if (is_null($user->account_id)) {
            $ids = $ids->merge(User::where('account_id', $user->id)->pluck('id'));
        }
        $ids = $ids->unique()->values();

        $count = 0;
        DB::transaction(function () use ($ids, &$count) {
            foreach ($ids as $id) {
                $u = User::with('uploads', 'policyDocuments')->find($id);
                if (!$u || $u->isAdmin()) {
                    continue;
                }
                foreach ($u->uploads as $up) {
                    Storage::disk('local')->delete($up->path);
                }
                foreach ($u->policyDocuments as $pd) {
                    Storage::disk('local')->delete($pd->path);
                }
                Storage::disk('local')->deleteDirectory('property-images/' . $id);
                $u->delete();
                $count++;
            }
        });

        return redirect()->route('admin.motels')->with('status', "🗑️ Deleted {$count} record(s).");
    }

    public function policies()
    {
        return view('admin.policies', ['members' => $this->members()]);
    }

    public function policyDownload(PolicyDocument $document)
    {
        $name = ($document->user->motel ?? 'motel') . '-' . $document->type . '.pdf';
        return Storage::disk('local')->download($document->path, $name);
    }

    public function uploadDownload(Upload $upload)
    {
        return Storage::disk('local')->download($upload->path, $upload->original_name);
    }
}
