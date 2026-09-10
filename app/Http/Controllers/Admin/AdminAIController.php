<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\CommunityMember;
use App\Models\Document;
use App\Models\Employer;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\JobSeeker;
use App\Models\Supplier;
use App\Models\SupplierRequest;
use App\Models\User;
use App\Services\WebFetcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Head-office AI assistant. Each turn is given a live snapshot of the whole
 * system (members, jobs, applicants, community, suppliers, requests…) so it can
 * look things up and answer questions, and it can build a report on any request.
 */
class AdminAIController extends Controller
{
    public function index(Request $r)
    {
        return view('admin.ai', [
            'configured' => (bool) config('rmc.ai.enabled'),
            'history'    => session('admin_ai_history', []),
            'reportId'   => $r->query('report'),
            'website'    => $r->query('website'),
        ]);
    }

    public function ask(Request $r)
    {
        $data = $r->validate([
            'message'   => ['nullable', 'string', 'max:6000'],
            'report_id' => ['nullable', 'integer'],
        ]);

        if (!config('rmc.ai.enabled')) {
            return response()->json(['reply' => "The AI isn't connected yet — add the Anthropic API key to the server and it'll come to life."]);
        }

        if (!empty($data['report_id'])) {
            $userMsg = $this->reportPrompt((int) $data['report_id']);
            $display = 'Build a report on request #' . $data['report_id'];
        } else {
            $userMsg = trim((string) ($data['message'] ?? ''));
            $display = $userMsg;
        }
        if ($userMsg === '') {
            return response()->json(['reply' => 'Ask me anything about the collective — members, jobs, applicants, the community, requests…']);
        }

        $history = session('admin_ai_history', []);
        $history[] = ['role' => 'user', 'content' => $userMsg];

        try {
            $reply = $this->askClaude($history);
        } catch (\Throwable $e) {
            Log::warning('AdminAI failed: ' . $e->getMessage());
            return response()->json(['reply' => "Sorry — I couldn't reach the AI just then. Please try again."]);
        }

        $history[] = ['role' => 'assistant', 'content' => $reply];
        session(['admin_ai_history' => array_slice($history, -16)]);

        return response()->json(['reply' => $reply, 'you' => $display]);
    }

    public function clear(Request $r)
    {
        $r->session()->forget('admin_ai_history');
        return response()->json(['ok' => true]);
    }

    /** Crawl an external website (homepage + key pages) and report on it. */
    public function websiteReport(Request $r, WebFetcher $web)
    {
        $data = $r->validate([
            'url'  => ['required', 'string', 'max:300'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (!config('rmc.ai.enabled')) {
            return response()->json(['reply' => "The AI isn't connected yet — add the Anthropic API key."]);
        }

        $pages = $web->crawl($data['url']);
        if (empty($pages)) {
            return response()->json([
                'reply' => "I couldn't read that site — it may be down, blocking automated readers, or the address is off. Double-check the URL" . (config('rmc.ai.scraper.key') ? '.' : ', and note a scraper key hasn\'t been added yet (works keyless but with tight rate limits).'),
                'you'   => 'Website report: ' . $data['url'],
            ]);
        }

        $corpus = '';
        foreach ($pages as $u => $text) {
            $corpus .= "\n\n===== PAGE: {$u} =====\n" . $text;
        }
        $corpus = mb_substr($corpus, 0, 24000);

        $system = "You are a web/marketing analyst for the Retro Motel Collective. You've been given the readable text of "
            . count($pages) . " page(s) crawled from a motel/hospitality website. Write a practical website report for head office with these sections: "
            . "Overview (what the site is & first impression), Content & messaging, Booking & contact (can a guest easily book/enquire? what's visible), "
            . "SEO & discoverability signals (from the content you can see), What's working, and Recommendations (specific, prioritised). "
            . "Be honest and concrete; only judge what's actually in the text. Plain Australian English.";
        $user = ($data['note'] ? "Context: {$data['note']}\n\n" : '')
            . "Crawled pages:\n" . $corpus;

        try {
            $reply = $this->askClaude([['role' => 'user', 'content' => $user]], $system);
        } catch (\Throwable $e) {
            Log::warning('AdminAI website report failed: ' . $e->getMessage());
            return response()->json(['reply' => "I read the site but couldn't generate the report just then — please try again.", 'you' => 'Website report: ' . $data['url']]);
        }

        $reply = 'Pages read: ' . implode(', ', array_keys($pages)) . "\n\n" . $reply;

        // Keep it out of the rolling chat context (reports are large/one-off).
        return response()->json(['reply' => $reply, 'you' => 'Website report: ' . $data['url']]);
    }

    /* ------------------------------------------------------------- Claude */

    private function askClaude(array $history, ?string $systemOverride = null): string
    {
        $system = $systemOverride ?? ("You are the operations AI for the Retro Motel Collective's head office (admin). "
            . "You have a live snapshot of the whole system below and should use it to answer questions, look things up, "
            . "summarise, and spot things that need attention. Be concise, specific and practical, in plain Australian English. "
            . "If asked for something not in the snapshot, say what you can see and suggest where to look.\n\n"
            . "=== LIVE SYSTEM SNAPSHOT ===\n" . $this->systemSnapshot());

        $resp = Http::withHeaders([
            'x-api-key'         => config('rmc.ai.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(45)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('rmc.ai.model'),
            'max_tokens' => 1500,
            'system'     => $system,
            'messages'   => array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $history),
        ]);

        if (!$resp->successful()) {
            throw new \RuntimeException('Claude HTTP ' . $resp->status() . ' ' . $resp->body());
        }
        return trim((string) data_get($resp->json(), 'content.0.text', 'No answer.'));
    }

    /* -------------------------------------------------------- system data */

    private function systemSnapshot(): string
    {
        $lines = [];

        // Members / motels
        $members = User::where('role', 'owner')->orderBy('motel')->get();
        $lines[] = 'MEMBERS / MOTELS (' . $members->count() . '):';
        foreach ($members as $m) {
            $lines[] = sprintf(
                '- %s | owner: %s <%s> | %s | band: %s | tier: %s | details: %s | registration: %d%% | joined: %s%s',
                $m->motel ?: $m->name,
                $m->name,
                $m->email,
                $m->loc ?: 'location n/a',
                $m->band()['label'] ?? $m->band,
                $m->tierMeta()['name'] ?? $m->tier,
                $m->details_complete ? 'complete' : 'pending',
                $m->overallPct(),
                $m->created_at?->format('M Y'),
                $m->admin_notes ? ' | notes: ' . \Illuminate\Support\Str::limit(str_replace("\n", ' ', $m->admin_notes), 160) : ''
            );
        }

        // Jobs
        $lines[] = '';
        $lines[] = 'JOBS: ' . JobListing::where('status', 'approved')->count() . ' approved/live, '
            . JobListing::where('status', 'pending')->count() . ' pending approval, '
            . JobListing::count() . ' total. Applications: ' . JobApplication::count() . '. Registered job seekers: ' . JobSeeker::count() . '.';

        // External employers
        $lines[] = 'EXTERNAL EMPLOYERS: ' . Employer::count() . ' accounts, '
            . Employer::sum('job_credits') . ' unused credits.';

        // Community
        $lines[] = 'COMMUNITY: ' . CommunityMember::count() . ' motels joined, '
            . ForumThread::count() . ' threads, ' . ForumReply::count() . ' replies.';

        // Suppliers + documents
        $lines[] = 'RESOURCE LIBRARY: ' . Document::count() . ' documents. SUPPLIERS: ' . Supplier::count()
            . ' listed, ' . SupplierRequest::where('status', 'new')->count() . ' open requests.';

        // Requests / notifications (recent)
        $unread = AdminNotification::whereNull('read_at')->count();
        $lines[] = '';
        $lines[] = 'OPEN REQUESTS / NOTIFICATIONS (' . $unread . ' unread). Recent:';
        foreach (AdminNotification::with('user')->orderByDesc('created_at')->limit(25)->get() as $n) {
            $lines[] = sprintf('- [%s] %s%s — %s (%s)',
                $n->read_at ? 'read' : 'UNREAD',
                $n->title,
                $n->user ? ' · ' . ($n->user->motel ?: $n->user->name) : '',
                \Illuminate\Support\Str::limit((string) $n->body, 140),
                $n->created_at?->diffForHumans()
            );
        }

        return implode("\n", $lines);
    }

    private function reportPrompt(int $notificationId): string
    {
        $n = AdminNotification::with('user')->find($notificationId);
        if (!$n) {
            return 'Build a short report — but note request #' . $notificationId . ' was not found.';
        }

        $ctx = "Request: {$n->title} (type: {$n->type})\n";
        $ctx .= 'Logged: ' . $n->created_at?->format('j M Y, g:ia') . "\n";
        if ($n->body) {
            $ctx .= "Details: {$n->body}\n";
        }
        if ($n->user) {
            $u = $n->user;
            $ctx .= sprintf(
                "Property: %s | owner %s <%s> | %s | band %s | tier %s | registration %d%% | details %s\n",
                $u->motel ?: $u->name, $u->name, $u->email, $u->loc ?: 'n/a',
                $u->band()['label'] ?? $u->band, $u->tierMeta()['name'] ?? $u->tier,
                $u->overallPct(), $u->details_complete ? 'complete' : 'pending'
            );
            if ($u->admin_notes) {
                $ctx .= 'Head-office notes: ' . str_replace("\n", ' ', $u->admin_notes) . "\n";
            }
        }

        return "Build a concise internal report for head office on this request. "
            . "Use clear sections: Summary, Context, What it means, Recommended next steps. Keep it practical.\n\n" . $ctx;
    }
}
