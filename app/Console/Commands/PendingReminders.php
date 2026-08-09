<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Outbox;
use Illuminate\Console\Command;

class PendingReminders extends Command
{
    protected $signature = 'rmc:pending-reminders';
    protected $description = 'Queue a reminder email to owners who registered but have not completed their details after N days.';

    public function handle(): int
    {
        $days = (int) config('rmc.pending_reminder_days', 7);
        $cutoff = now()->subDays($days);

        $users = User::where('role', 'owner')
            ->where('details_complete', false)
            ->whereNull('pending_reminded_at')
            ->where('created_at', '<=', $cutoff)
            ->get();

        foreach ($users as $u) {
            Outbox::pendingReminder($u);
            $u->pending_reminded_at = now();
            $u->save();
        }

        $this->info('Queued ' . $users->count() . ' pending reminder(s).');
        return self::SUCCESS;
    }
}
