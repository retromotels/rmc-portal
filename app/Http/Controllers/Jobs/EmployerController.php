<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\Employer;
use App\Models\JobListing;
use App\Models\User;
use App\Services\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployerController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.external_jobs'), 404);
    }

    private function employer(): ?Employer
    {
        return Auth::guard('employer')->user();
    }

    /** Public pricing / list-a-job landing. */
    public function pricing()
    {
        $this->guard();
        return view('jobs.employers.pricing', [
            'tiers'    => config('rmc.external_jobs.tiers'),
            'currency' => strtoupper(config('rmc.external_jobs.currency')),
            'employer' => $this->employer(),
        ]);
    }

    public function dashboard()
    {
        $this->guard();
        if (!($employer = $this->employer())) {
            return redirect()->route('employer.login');
        }

        return view('jobs.employers.dashboard', [
            'employer'  => $employer,
            'jobs'      => $employer->jobs()->withCount('applications')->latest()->get(),
            'purchases' => $employer->purchases()->latest()->get(),
            'tiers'     => config('rmc.external_jobs.tiers'),
            'currency'  => strtoupper(config('rmc.external_jobs.currency')),
        ]);
    }

    public function createJob()
    {
        $this->guard();
        if (!($employer = $this->employer())) {
            return redirect()->route('employer.login');
        }
        if ($employer->job_credits < 1) {
            return redirect()->route('employer.dashboard')->with('flash', 'You need a job credit first — grab a pack to post.');
        }
        return view('jobs.employers.post', ['job' => new JobListing(['employment_type' => 'full-time'])]);
    }

    public function storeJob(Request $r)
    {
        $this->guard();
        if (!($employer = $this->employer())) {
            return redirect()->route('employer.login');
        }
        if ($employer->job_credits < 1) {
            return redirect()->route('employer.dashboard')->with('flash', 'You need a job credit first.');
        }

        $data = $r->validate([
            'title'           => ['required', 'string', 'max:120'],
            'employment_type' => ['required', 'in:full-time,part-time,casual,contract'],
            'department'      => ['nullable', 'string', 'max:40'],
            'location'        => ['nullable', 'string', 'max:160'],
            'state'           => ['nullable', 'string', 'max:8'],
            'pay'             => ['nullable', 'string', 'max:80'],
            'salary_annual'   => ['nullable', 'integer', 'min:0'],
            'description'     => ['required', 'string', 'max:6000'],
            'closes_at'       => ['nullable', 'date'],
        ]);

        $admin = User::where('role', 'admin')->orderBy('id')->first();

        $job = null;
        DB::transaction(function () use ($employer, $data, $admin, &$job) {
            $employer->decrement('job_credits');
            $job = JobListing::create($data + [
                'user_id'     => $admin->id,
                'employer_id' => $employer->id,
                'employer'    => $employer->company,
                'source'      => 'external',
                'status'      => 'pending',
            ]);
        });

        Notify::admin('job_pending', 'External job awaiting approval',
            $employer->company . ' (external employer) submitted "' . $job->title . '".',
            'An external employer submitted a paid job listing');

        return redirect()->route('employer.dashboard')
            ->with('flash', 'Job submitted — it goes live once head office approves it. 1 credit used.');
    }
}
