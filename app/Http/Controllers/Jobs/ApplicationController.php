<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use App\Services\Outbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ApplicationController extends Controller
{
    public function create(string $slug)
    {
        if (!Auth::guard('seeker')->check()) {
            session(['url.intended' => route('jobs.apply', $slug)]);
            return redirect()->route('seeker.register');
        }
        $job = JobListing::live()->where('slug', $slug)->with('property')->firstOrFail();
        $seeker = Auth::guard('seeker')->user();
        $applied = JobApplication::where('job_listing_id', $job->id)
            ->where('job_seeker_id', $seeker->id)->exists();

        return view('jobs.public.apply', ['job' => $job, 'seeker' => $seeker, 'applied' => $applied]);
    }

    public function store(Request $r, string $slug)
    {
        if (!Auth::guard('seeker')->check()) {
            return redirect()->route('seeker.login');
        }
        $job = JobListing::live()->where('slug', $slug)->firstOrFail();
        $seeker = Auth::guard('seeker')->user();

        $data = $r->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:190'],
            'phone'   => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:4000'],
            'cv'      => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:6144'],
        ]);

        $cvPath = $r->hasFile('cv') ? $r->file('cv')->store('cvs', 'local') : null;

        $app = JobApplication::create([
            'job_listing_id' => $job->id,
            'job_seeker_id'  => $seeker->id,
            'name'           => $data['name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'message'        => $data['message'] ?? null,
            'cv_path'        => $cvPath,
            'status'         => 'new',
        ]);

        $this->notify($job, $app);

        return redirect()->route('jobs.public.show', $job->slug)->with('applied', $job->title);
    }

    public function dashboard()
    {
        if (!Auth::guard('seeker')->check()) {
            return redirect()->route('seeker.login');
        }
        $seeker = Auth::guard('seeker')->user();
        return view('jobs.public.dashboard', [
            'seeker' => $seeker,
            'apps'   => $seeker->applications()->with('job.property')->latest()->get(),
        ]);
    }

    /** Email the applicant's details to the property's account contact + admin. */
    private function notify(JobListing $job, JobApplication $app): void
    {
        $prop = $job->property;
        $acct = User::find($prop->account_id ?: $prop->id);
        $subject = 'New application: ' . $job->title . ' — ' . ($prop->motel ?: 'your property');
        $html = View::make('emails.job_application', ['job' => $job, 'app' => $app, 'prop' => $prop])->render();
        $meta = ['job' => $job->title, 'motel' => $prop->motel, 'applicant' => $app->name];

        if ($acct && $acct->email && !str_contains($acct->email, 'properties.retromotels.local')) {
            Outbox::queue('job_application', $acct->email, $acct->name, $subject, $html, $meta);
        }
        foreach ((array) config('rmc.admin_emails') as $adminEmail) {
            Outbox::queue('job_application', $adminEmail, 'RMC Admin', $subject, $html, $meta);
        }
    }
}
