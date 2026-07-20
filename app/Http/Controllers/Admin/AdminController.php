<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyDocument;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\Request;
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
