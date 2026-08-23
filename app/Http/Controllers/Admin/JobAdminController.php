<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobAdminController extends Controller
{
    public function index(Request $r)
    {
        $status = $r->query('status', 'pending');
        $q = JobListing::with('property')->withCount('applications')->latest();
        if (in_array($status, ['pending', 'approved', 'rejected', 'closed'], true)) {
            $q->where('status', $status);
        }

        return view('admin.jobs.index', [
            'jobs'   => $q->paginate(30)->withQueryString(),
            'status' => $status,
            'counts' => JobListing::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function approve(JobListing $job)
    {
        $job->update(['status' => 'approved', 'approved_at' => now(), 'reject_reason' => null]);
        return back()->with('status', 'Approved — "' . $job->title . '" is now live on the job board.');
    }

    public function reject(Request $r, JobListing $job)
    {
        $data = $r->validate(['reject_reason' => ['nullable', 'string', 'max:255']]);
        $job->update(['status' => 'rejected', 'reject_reason' => $data['reject_reason'] ?? null]);
        return back()->with('status', 'Rejected — the property can edit and resubmit.');
    }

    public function applicants(JobListing $job)
    {
        $job->load('applications', 'property');
        return view('admin.jobs.applicants', ['job' => $job]);
    }
}
