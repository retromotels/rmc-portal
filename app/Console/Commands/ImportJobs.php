<?php

namespace App\Console\Commands;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Import a batch of admin-owned job listings from a JSON file. Jobs are
 * attached to the admin user (not a member property), carry an employer name,
 * and are created already approved so they show on the public board. Idempotent
 * by source + source_ref, so it can be re-run safely.
 */
class ImportJobs extends Command
{
    protected $signature = 'rmc:import-jobs {file=database/data/seek_jobs.json} {--source=seek}';
    protected $description = 'Import admin-posted job listings from a JSON file.';

    public function handle(): int
    {
        $admin = User::where('role', 'admin')->orderBy('id')->first();
        if (!$admin) {
            $this->error('No admin user found to own the jobs.');
            return self::FAILURE;
        }

        $path = base_path($this->argument('file'));
        if (!is_file($path)) {
            $this->error('File not found: ' . $path);
            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($path), true);
        if (!is_array($rows)) {
            $this->error('Could not parse JSON.');
            return self::FAILURE;
        }

        $source = (string) $this->option('source');
        $created = 0;
        $updated = 0;

        foreach ($rows as $r) {
            $ref = $r['source_ref'] ?? null;
            $state = isset($r['state']) ? strtoupper(trim((string) $r['state'])) : null;
            $salary = isset($r['salary_annual']) && $r['salary_annual'] !== null ? (int) $r['salary_annual'] : null;

            // Already imported: backfill the state + salary columns without disturbing
            // its status (an admin may have edited or closed it since). Idempotent.
            if ($ref) {
                $existing = JobListing::where('source', $source)->where('source_ref', $ref)->first();
                if ($existing) {
                    $existing->update(['state' => $state ?: null, 'salary_annual' => $salary]);
                    $updated++;
                    continue;
                }
            }

            $type = $r['employment_type'] ?? 'full-time';
            JobListing::create([
                'user_id'         => $admin->id,
                'employer'        => $r['employer'] ?? null,
                'source'          => $source,
                'source_ref'      => $ref,
                'title'           => $r['title'] ?? 'Role',
                'employment_type' => in_array($type, ['full-time', 'part-time', 'casual', 'contract'], true) ? $type : 'full-time',
                'department'      => $r['department'] ?? null,
                'location'        => $r['location'] ?? null,
                'state'           => $state ?: null,
                'pay'             => $r['pay'] ?? null,
                'salary_annual'   => $salary,
                'description'     => $r['description'] ?? '',
                'status'          => 'approved',
                'approved_at'     => now(),
            ]);
            $created++;
        }

        $this->info("Imported {$created} new jobs, backfilled {$updated} existing. Owner: {$admin->email}, source: {$source}.");
        return self::SUCCESS;
    }
}
