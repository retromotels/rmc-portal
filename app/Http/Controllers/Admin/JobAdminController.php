<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

    /** Admin composes a new listing (optionally on behalf of a member property). */
    public function create()
    {
        return view('admin.jobs.form', [
            'job'        => new JobListing(['employment_type' => 'full-time', 'status' => 'approved']),
            'properties' => $this->properties(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);
        $propertyId = $data['property_id'] ?? null;
        unset($data['property_id']);

        $admin = User::where('role', 'admin')->orderBy('id')->first();

        if ($propertyId) {
            $prop = User::where('role', 'owner')->findOrFail($propertyId);
            $data['user_id']  = $prop->id;
            $data['employer'] = null;
            $data['location'] = $data['location'] ?: $prop->loc;
            $data['state']    = $data['state'] ?: null;
        } else {
            $data['user_id']  = $admin->id;
            $data['source']   = 'admin';
        }

        $data['status'] = 'approved';
        $data['approved_at'] = now();

        $job = JobListing::create($data);

        return redirect()->route('admin.jobs', ['status' => 'approved'])
            ->with('status', 'Job created and published — "' . $job->title . '" is live on the board.');
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
        $job->load([
            'property',
            'applications' => fn ($a) => $a->with(['seeker' => fn ($s) => $s->withCount('applications')])->latest(),
        ]);

        return view('admin.jobs.applicants', ['job' => $job]);
    }

    /** Download a specific application's uploaded CV. */
    public function cvDownload(JobApplication $application)
    {
        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);
        $ext = pathinfo($application->cv_path, PATHINFO_EXTENSION) ?: 'pdf';
        return Storage::disk('local')->download($application->cv_path, \Illuminate\Support\Str::slug($application->name ?: 'applicant') . '-cv.' . $ext);
    }

    private function properties()
    {
        return User::where('role', 'owner')->orderBy('motel')->get(['id', 'motel', 'name', 'loc']);
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'title'           => ['required', 'string', 'max:120'],
            'employer'        => ['nullable', 'string', 'max:160'],
            'property_id'     => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'owner')],
            'employment_type' => ['required', 'in:full-time,part-time,casual,contract'],
            'department'      => ['nullable', Rule::in(array_keys(config('rmc.job_departments')))],
            'location'        => ['nullable', 'string', 'max:160'],
            'state'           => ['nullable', Rule::in(array_keys(config('rmc.job_states')))],
            'pay'             => ['nullable', 'string', 'max:80'],
            'salary_annual'   => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'description'     => ['required', 'string', 'max:6000'],
            'closes_at'       => ['nullable', 'date'],
        ]);
    }
}
