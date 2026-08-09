<?php

namespace App\Http\Controllers;

use App\Models\PolicyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return view('account', [
            'user' => $this->currentProperty()->load('policyDocuments'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'motel' => ['required', 'string', 'max:255'],
            'bio'   => ['nullable', 'string', 'max:2000'],
            'loc'   => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $property = $this->currentProperty();

        if ($request->hasFile('photo')) {
            if ($property->photo_path) Storage::disk('public')->delete($property->photo_path);
            $data['photo_path'] = $request->file('photo')->store('avatars', 'public');
        }
        unset($data['photo']);

        $property->update($data);

        // Keep the account owner's name in sync (name is account-level).
        $account = auth()->user();
        if ($account->id !== $property->id) {
            $account->update(['name' => $data['name']]);
        }

        return back()->with('status', 'Profile saved.');
    }

    public function policyDownload(Request $request, PolicyDocument $document)
    {
        abort_unless($this->accountPropertyIds()->contains($document->user_id), 403);
        return Storage::disk('local')->download($document->path, $document->title . '.pdf');
    }

    public function requestCancellation(Request $request)
    {
        $this->currentProperty()->update(['cancel_requested_at' => now()]);
        return back()->with('status', 'Cancellation request sent to RMC. Our team will confirm by email.');
    }
}
