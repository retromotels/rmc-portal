<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Services\AiJobSearch;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
    public function index(Request $r, AiJobSearch $ai)
    {
        $type  = $r->query('type');
        $dept  = $r->query('dept');
        $state = strtoupper((string) $r->query('state'));
        $pay   = (string) $r->query('pay');
        $kw    = trim((string) $r->query('q'));

        // Natural-language search overrides the individual filters.
        $aiQuery = trim((string) $r->query('ai'));
        if ($aiQuery !== '') {
            $parsed = $ai->parse($aiQuery);
            $type  = $parsed['type'];
            $dept  = $parsed['dept'];
            $state = $parsed['state'];
            $pay   = $parsed['pay'];
            $kw    = $parsed['kw'];
        }

        $q = JobListing::live()->with('property')->latest('approved_at');

        if (array_key_exists((string) $type, config('rmc.employment_types'))) {
            $q->where('employment_type', $type);
        }
        if (array_key_exists((string) $dept, config('rmc.job_departments'))) {
            $q->where('department', $dept);
        }
        if (array_key_exists($state, config('rmc.job_states'))) {
            $q->where('state', $state);
        }
        if (array_key_exists($pay, config('rmc.salary_bands'))) {
            $q->where('salary_annual', '>=', (int) $pay);
        }
        if ($kw !== '') {
            $q->where(fn ($w) => $w->where('title', 'like', "%{$kw}%")
                ->orWhere('description', 'like', "%{$kw}%")
                ->orWhere('location', 'like', "%{$kw}%"));
        }

        return view('jobs.public.index', [
            'jobs'    => $q->paginate(24)->withQueryString(),
            'type'    => $type,
            'dept'    => $dept,
            'state'   => array_key_exists($state, config('rmc.job_states')) ? $state : '',
            'pay'     => array_key_exists($pay, config('rmc.salary_bands')) ? $pay : '',
            'kw'      => $kw,
            'aiQuery' => $aiQuery,
        ]);
    }

    public function show(string $slug)
    {
        $job = JobListing::live()->where('slug', $slug)->with('property')->firstOrFail();

        return view('jobs.public.show', ['job' => $job]);
    }
}
