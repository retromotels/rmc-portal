<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Head-office CRM of registered job seekers. Lists every account with their
 * details, application activity and — where they've uploaded one on an
 * application — a downloadable resume.
 */
class SeekerAdminController extends Controller
{
    public function index(Request $r)
    {
        $kw    = trim((string) $r->query('q'));
        $state = strtoupper((string) $r->query('state'));

        $q = JobSeeker::withCount('applications')
            ->with(['applications' => fn ($a) => $a->with('job:id,title,slug')->latest()])
            ->latest();

        if ($kw !== '') {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$kw}%")->orWhere('email', 'like', "%{$kw}%"));
        }
        if (array_key_exists($state, config('rmc.job_states'))) {
            $q->where('state', $state);
        }

        return view('admin.seekers.index', [
            'seekers'     => $q->paginate(50)->withQueryString(),
            'kw'          => $kw,
            'state'       => array_key_exists($state, config('rmc.job_states')) ? $state : '',
            'total'       => JobSeeker::count(),
            'withResume'  => JobSeeker::whereHas('applications', fn ($a) => $a->whereNotNull('cv_path'))->count(),
        ]);
    }

    /** Download the seeker's most recent uploaded resume. */
    public function cvDownload(JobSeeker $seeker)
    {
        $app = $seeker->latestCvApplication();
        abort_unless($app && $app->cv_path && Storage::disk('local')->exists($app->cv_path), 404);

        $ext = pathinfo($app->cv_path, PATHINFO_EXTENSION) ?: 'pdf';
        return Storage::disk('local')->download($app->cv_path, Str::slug($seeker->name ?: 'resume') . '-cv.' . $ext);
    }
}
