<?php

namespace App\Http\Controllers;

use App\Models\ChatWidget;
use Illuminate\Http\Request;

class ChatWidgetController extends Controller
{
    public function edit()
    {
        $w = ChatWidget::forProperty($this->currentProperty());

        return view('tools.chat-widget', [
            'w'       => $w,
            'src'     => url('/widget/' . $w->token . '.js'),
        ]);
    }

    public function update(Request $r)
    {
        $prop = $this->currentProperty();
        $w = ChatWidget::forProperty($prop);

        $labels  = (array) $r->input('e_label', []);
        $keys    = (array) $r->input('e_keys', []);
        $answers = (array) $r->input('e_answer', []);

        $entries = [];
        foreach ($answers as $i => $ans) {
            $ans = trim((string) $ans);
            $kw  = trim((string) ($keys[$i] ?? ''));
            $lab = trim((string) ($labels[$i] ?? ''));
            if ($ans === '' && $kw === '' && $lab === '') {
                continue;
            }
            $entries[] = ['label' => $lab, 'keys' => $kw, 'answer' => $ans];
        }

        $config = [
            'title'    => trim((string) $r->input('title')) ?: ($prop->motel ?: 'Guest concierge'),
            'subtitle' => trim((string) $r->input('subtitle')) ?: 'Guest concierge · ask me anything',
            'welcome'  => trim((string) $r->input('welcome')),
            'primary'  => $this->hex($r->input('primary'), '#1E7F86'),
            'accent'   => $this->hex($r->input('accent'), '#E8553D'),
            'entries'  => $entries,
        ];

        $w->update(['enabled' => $r->boolean('enabled'), 'config' => $config]);

        return redirect()->route('tools.chat-widget')->with('status', 'Chat widget saved — your embed updates automatically.');
    }

    private function hex($v, string $default): string
    {
        $v = (string) $v;
        return preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? $v : $default;
    }
}
