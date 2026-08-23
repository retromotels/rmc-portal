<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Services\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Property-side job listings. A property drafts/submits a job; it lands as
 * 'pending' for admin approval, then shows on the public board once approved.
 */
class JobController extends Controller
{
    public function index()
    {
        $ids = $this->accountPropertyIds();
        $jobs = JobListing::whereIn('user_id', $ids)->withCount('applications')->latest()->get();

        return view('jobs.index', ['jobs' => $jobs]);
    }

    public function create()
    {
        return view('jobs.form', ['job' => new JobListing(['employment_type' => 'full-time'])]);
    }

    public function store(Request $r)
    {
        $prop = $this->currentProperty();
        $job = JobListing::create($this->validated($r) + [
            'user_id'  => $prop->id,
            'location' => $prop->loc,
            'status'   => 'pending',
        ]);

        Notify::admin('job_pending', 'Job awaiting approval',
            ($prop->motel ?: $prop->name) . ' submitted the listing "' . $job->title . '".',
            'A property submitted a job listing for approval');

        return redirect()->route('jobs.index')
            ->with('status', 'Job submitted — it goes live on the board once head office approves it.');
    }

    public function edit(JobListing $job)
    {
        $this->authorizeJob($job);
        return view('jobs.form', ['job' => $job]);
    }

    public function update(Request $r, JobListing $job)
    {
        $this->authorizeJob($job);
        $job->update($this->validated($r) + ['status' => 'pending', 'approved_at' => null, 'reject_reason' => null]);

        Notify::admin('job_pending', 'Job edited — re-approval needed',
            ($job->property->motel ?? 'A property') . ' edited "' . $job->title . '".',
            'A property edited a job listing');

        return redirect()->route('jobs.index')
            ->with('status', 'Saved — your changes go live after head office re-approves.');
    }

    public function close(JobListing $job)
    {
        $this->authorizeJob($job);
        $job->update(['status' => 'closed']);
        return back()->with('status', 'Job closed — removed from the public board.');
    }

    public function destroy(JobListing $job)
    {
        $this->authorizeJob($job);
        $job->delete();
        return back()->with('status', 'Job deleted.');
    }

    public function applicants(JobListing $job)
    {
        $this->authorizeJob($job);
        $job->load('applications');
        return view('jobs.applicants', ['job' => $job]);
    }

    public function cvDownload(JobApplication $application)
    {
        $job = $application->job;
        abort_unless($job && $this->accountPropertyIds()->contains($job->user_id), 403);
        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);
        return Storage::disk('local')->download($application->cv_path);
    }

    private function authorizeJob(JobListing $job): void
    {
        abort_unless($this->accountPropertyIds()->contains($job->user_id), 403);
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'title'           => ['required', 'string', 'max:120'],
            'employment_type' => ['required', 'in:full-time,part-time,casual,contract'],
            'department'      => ['nullable', 'string', 'max:40'],
            'pay'             => ['nullable', 'string', 'max:80'],
            'description'     => ['required', 'string', 'max:6000'],
            'closes_at'       => ['nullable', 'date'],
        ]);
    }
}
