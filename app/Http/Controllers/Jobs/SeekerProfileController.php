<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\JobSeekerResume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SeekerProfileController extends Controller
{
    /** The signed-in seeker, or null. */
    private function seeker(): ?JobSeeker
    {
        return Auth::guard('seeker')->user();
    }

    public function show()
    {
        if (!$this->seeker()) {
            return redirect()->route('seeker.login');
        }
        $seeker = $this->seeker()->load('resumes');

        return view('jobs.public.profile', ['seeker' => $seeker]);
    }

    public function update(Request $r)
    {
        if (!($seeker = $this->seeker())) {
            return redirect()->route('seeker.login');
        }

        $data = $r->validate([
            'name'     => ['required', 'string', 'max:120'],
            'phone'    => ['nullable', 'string', 'max:40'],
            'state'    => ['nullable', Rule::in(array_keys(config('rmc.job_states')))],
            'town'     => ['nullable', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:140'],
            'bio'      => ['nullable', 'string', 'max:2000'],
        ]);

        $seeker->update($data);

        return back()->with('flash', 'Profile saved.');
    }

    public function uploadAvatar(Request $r)
    {
        if (!($seeker = $this->seeker())) {
            return redirect()->route('seeker.login');
        }

        $r->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);

        if ($seeker->avatar_path) {
            Storage::disk('local')->delete($seeker->avatar_path);
        }
        $path = $r->file('avatar')->store('avatars', 'local');
        $seeker->update(['avatar_path' => $path]);

        return back()->with('flash', 'Photo updated.');
    }

    /** Stream a seeker's avatar inline (private disk, no public symlink needed). */
    public function avatar(JobSeeker $seeker)
    {
        abort_unless($seeker->avatar_path && Storage::disk('local')->exists($seeker->avatar_path), 404);

        return Storage::disk('local')->response($seeker->avatar_path);
    }

    public function addResume(Request $r)
    {
        if (!($seeker = $this->seeker())) {
            return redirect()->route('seeker.login');
        }

        $r->validate(['resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:6144']]);

        $file = $r->file('resume');
        $path = $file->store('resumes', 'local');
        $first = $seeker->resumes()->count() === 0;

        $seeker->resumes()->create([
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'size'          => $file->getSize(),
            'is_default'    => $first, // first resume becomes the default automatically
        ]);

        return back()->with('flash', 'Resume added.');
    }

    public function setDefaultResume(JobSeekerResume $resume)
    {
        if (!($seeker = $this->seeker()) || $resume->job_seeker_id !== $seeker->id) {
            abort(403);
        }

        $seeker->resumes()->update(['is_default' => false]);
        $resume->update(['is_default' => true]);

        return back()->with('flash', 'Default resume updated.');
    }

    public function deleteResume(JobSeekerResume $resume)
    {
        if (!($seeker = $this->seeker()) || $resume->job_seeker_id !== $seeker->id) {
            abort(403);
        }

        Storage::disk('local')->delete($resume->path);
        $wasDefault = $resume->is_default;
        $resume->delete();

        if ($wasDefault && ($next = $seeker->resumes()->first())) {
            $next->update(['is_default' => true]);
        }

        return back()->with('flash', 'Resume removed.');
    }
}
