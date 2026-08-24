<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Assist — an in-portal Claude assistant for members. Admin-toggleable via
 * the Modules setting; answers only when the Anthropic API key is configured,
 * otherwise it says so plainly.
 */
class AIAssistController extends Controller
{
    private function guard(): void
    {
        abort_unless(Setting::bool('module_ai_assist'), 404);
    }

    public function index()
    {
        $this->guard();
        return view('tools.ai-assist', [
            'configured' => (bool) config('rmc.ai.enabled'),
            'history'    => session('ai_assist_history', []),
        ]);
    }

    public function ask(Request $r)
    {
        $this->guard();
        $data = $r->validate(['message' => ['required', 'string', 'max:4000']]);

        if (!config('rmc.ai.enabled')) {
            return response()->json(['reply' => "AI Assist isn't switched on yet — head office is adding the key that powers it. Check back soon."]);
        }

        $history = session('ai_assist_history', []);
        $history[] = ['role' => 'user', 'content' => $data['message']];

        try {
            $reply = $this->askClaude($history);
        } catch (\Throwable $e) {
            Log::warning('AIAssist failed: ' . $e->getMessage());
            return response()->json(['reply' => "Sorry — I couldn't reach the assistant just then. Please try again in a moment."]);
        }

        $history[] = ['role' => 'assistant', 'content' => $reply];
        $history = array_slice($history, -20); // keep last 10 exchanges
        session(['ai_assist_history' => $history]);

        return response()->json(['reply' => $reply]);
    }

    public function clear(Request $r)
    {
        $this->guard();
        $r->session()->forget('ai_assist_history');
        return response()->json(['ok' => true]);
    }

    private function askClaude(array $history): string
    {
        $prop = $this->currentProperty();
        $system = "You are AI Assist for the Retro Motels Collective — a warm, practical assistant for people who run independent Australian motels. "
            . "Help with marketing copy, guest messages and replies, operations, pricing ideas, listing and review advice, and general small-business questions. "
            . "Be concise and actionable, use plain Australian English, and give ready-to-use text when asked. "
            . "The member's property is: " . ($prop->motel ?: $prop->name) . ($prop->loc ? " in {$prop->loc}" : '') . ".";

        $resp = Http::withHeaders([
            'x-api-key'         => config('rmc.ai.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(40)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('rmc.ai.model'),
            'max_tokens' => 1200,
            'system'     => $system,
            'messages'   => array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $history),
        ]);

        if (!$resp->successful()) {
            throw new \RuntimeException('Claude HTTP ' . $resp->status());
        }

        return trim((string) data_get($resp->json(), 'content.0.text', 'Sorry, I had trouble answering that.'));
    }
}
