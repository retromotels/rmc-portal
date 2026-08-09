<?php

namespace App\Console\Commands;

use App\Models\OutboxEmail;
use App\Services\Mailer;
use Illuminate\Console\Command;

class FlushOutbox extends Command
{
    protected $signature = 'rmc:flush-outbox';
    protected $description = 'Attempt to send any queued outbox emails via SendGrid.';

    public function handle(): int
    {
        $sent = 0;
        foreach (OutboxEmail::where('status', 'queued')->get() as $e) {
            if (Mailer::deliver($e)) $sent++;
        }
        $this->info("Sent {$sent} queued email(s).");
        return self::SUCCESS;
    }
}
