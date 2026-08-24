<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\Employer;
use App\Models\JobPurchase;
use App\Services\Outbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

/**
 * External-employer billing. Fixed packs check out via Stripe when keys are set;
 * until then (dormant) a purchase becomes an invoice request that emails head
 * office. Top Shelf is always an enquiry. Credits are granted on confirmed
 * payment (verified against Stripe on return — no webhook needed).
 */
class EmployerBillingController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.external_jobs'), 404);
    }

    private function employer(): ?Employer
    {
        return Auth::guard('employer')->user();
    }

    public function checkout(Request $r, string $tier)
    {
        $this->guard();
        if (!($employer = $this->employer())) {
            return redirect()->route('employer.login');
        }

        $tiers = config('rmc.external_jobs.tiers');
        abort_unless(isset($tiers[$tier]) && $tier !== 'top_shelf', 404);
        $cfg = $tiers[$tier];
        $currency = config('rmc.external_jobs.currency');

        // Stripe live? Create a Checkout Session and send them to Stripe.
        if (config('rmc.stripe.live') && !empty($cfg['stripe_price'])) {
            $purchase = JobPurchase::create([
                'employer_id' => $employer->id,
                'tier'        => $tier,
                'credits'     => $cfg['credits'],
                'amount'      => $cfg['price'],
                'currency'    => $currency,
                'status'      => 'pending',
            ]);

            $resp = Http::withToken(config('rmc.stripe.secret'))->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'line_items' => [['price' => $cfg['stripe_price'], 'quantity' => 1]],
                'success_url' => route('employer.buy.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('employer.buy.cancel'),
                'client_reference_id' => (string) $employer->id,
                'customer_email' => $employer->email,
                'metadata' => ['purchase_id' => (string) $purchase->id, 'tier' => $tier],
            ]);

            if ($resp->successful() && ($url = $resp->json('url'))) {
                $purchase->update(['stripe_session_id' => $resp->json('id')]);
                return redirect()->away($url);
            }

            $purchase->update(['status' => 'error']);
            return back()->with('flash', 'Payment could not be started — please try again or contact us.');
        }

        // Dormant: log an invoice request and email head office.
        $purchase = JobPurchase::create([
            'employer_id' => $employer->id,
            'tier'        => $tier,
            'credits'     => $cfg['credits'],
            'amount'      => $cfg['price'],
            'currency'    => $currency,
            'status'      => 'requested',
        ]);
        $this->emailAdmin($employer, $cfg['name'] . ' pack requested (card payments not yet live)', $cfg, null);

        return redirect()->route('employer.dashboard')
            ->with('flash', "Thanks — we've received your request for the {$cfg['name']} pack. Card payments are being switched on; head office will be in touch to arrange it.");
    }

    public function success(Request $r)
    {
        $this->guard();
        $sessionId = (string) $r->query('session_id');
        $purchase = JobPurchase::where('stripe_session_id', $sessionId)->first();

        if ($purchase && $purchase->status !== 'paid' && config('rmc.stripe.live')) {
            $resp = Http::withToken(config('rmc.stripe.secret'))->get('https://api.stripe.com/v1/checkout/sessions/' . $sessionId);
            if ($resp->successful() && $resp->json('payment_status') === 'paid') {
                $purchase->update(['status' => 'paid']);
                $purchase->employer->increment('job_credits', $purchase->credits);
            }
        }

        return redirect()->route('employer.dashboard')->with('flash',
            $purchase && $purchase->status === 'paid'
                ? "Payment received — {$purchase->credits} job credit(s) added. Post away!"
                : 'Thanks! If payment completed your credits will appear shortly.');
    }

    public function cancel()
    {
        $this->guard();
        return redirect()->route('employer.dashboard')->with('flash', 'Checkout cancelled — no charge made.');
    }

    /** Top Shelf enquiry. */
    public function enquire(Request $r)
    {
        $this->guard();
        if (!($employer = $this->employer())) {
            return redirect()->route('employer.login');
        }
        $data = $r->validate(['note' => ['nullable', 'string', 'max:2000']]);

        JobPurchase::create([
            'employer_id' => $employer->id,
            'tier'        => 'top_shelf',
            'credits'     => 0,
            'amount'      => 0,
            'currency'    => config('rmc.external_jobs.currency'),
            'status'      => 'enquiry',
            'note'        => $data['note'] ?? null,
        ]);
        $this->emailAdmin($employer, 'Top Shelf enquiry', config('rmc.external_jobs.tiers.top_shelf'), $data['note'] ?? null);

        return redirect()->route('employer.dashboard')->with('flash', "Thanks — your Top Shelf enquiry is in. We'll send the full options through shortly.");
    }

    private function emailAdmin(Employer $employer, string $subject, array $cfg, ?string $note): void
    {
        $html = '<div style="font-family:Arial,sans-serif;font-size:15px;color:#222">'
            . '<h2 style="color:#9C3A1C">' . e($subject) . '</h2>'
            . '<p><strong>' . e($employer->company) . '</strong>'
            . ($employer->name ? ' (' . e($employer->name) . ')' : '') . '</p>'
            . '<p>Email: <a href="mailto:' . e($employer->email) . '">' . e($employer->email) . '</a>'
            . ($employer->phone ? '<br>Phone: ' . e($employer->phone) : '')
            . ($employer->website ? '<br>Web: ' . e($employer->website) : '') . '</p>'
            . '<p>Tier: <strong>' . e($cfg['name']) . '</strong>'
            . (isset($cfg['price']) && $cfg['price'] ? ' — A$' . e($cfg['price']) : '') . '</p>'
            . ($note ? '<p style="background:#f7f2e8;padding:10px 12px;border-radius:8px">' . nl2br(e($note)) . '</p>' : '')
            . '<p style="color:#888;font-size:12px">Retro Motels · external job posting</p></div>';

        foreach ((array) config('rmc.admin_emails') as $adminEmail) {
            Outbox::queue('employer_enquiry', $adminEmail, 'RMC Admin', 'RMC Jobs — ' . $subject . ': ' . $employer->company, $html, [
                'company' => $employer->company,
                'tier'    => $cfg['name'],
            ]);
        }
    }
}
