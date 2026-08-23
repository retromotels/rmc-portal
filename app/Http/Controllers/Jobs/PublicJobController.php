<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
    public function index(Request $r)
    {
        $type = $r->query('type');
        $dept = $r->query('dept');
        $kw   = trim((string) $r->query('q'));

        $q = JobListing::live()->with('property')->latest('approved_at');

        if (array_key_exists((string) $type, config('rmc.employment_types'))) {
            $q->where('employment_type', $type);
        }
        if (array_key_exists((string) $dept, config('rmc.job_departments'))) {
            $q->where('department', $dept);
        }
        if ($kw !== '') {
            $q->where(fn ($w) => $w->where('title', 'like', "%{$kw}%")
                ->orWhere('description', 'like', "%{$kw}%")
                ->orWhere('location', 'like', "%{$kw}%"));
        }

        return view('jobs.public.index', [
            'jobs'  => $q->get(),
            'total' => JobListing::live()->count(),
            'type'  => $type,
            'dept'  => $dept,
            'kw'    => $kw,
        ]);
    }

    public function show(string $slug)
    {
        $job = JobListing::live()->where('slug', $slug)->with('property')->firstOrFail();

        return view('jobs.public.show', ['job' => $job]);
    }
}
