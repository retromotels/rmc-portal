<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class SeedFaq extends Command
{
    protected $signature = 'rmc:seed-faq';
    protected $description = 'Load the standard Retro Motels FAQ set into the editable FAQ section.';

    public function handle(): int
    {
        $faq = [
            ['q' => 'Who is behind Retro Motels?', 'a' => <<<'A'
Two people you can call, not a faceless platform.
Jeremy Colahan spent his career inside the big end of hospitality — Marriott, Accor, IHG, InterContinental and 25hours, including GM roles at Hotel X and Mondrian. He saw exactly what chains pay for electricity, insurance, linen, tech and OTA commissions — and what independents are charged for the same things. Retro Motels exists to close that gap.
Luke Young is Jeremy's business partner and the builder behind the collective's technology. Through his digital agency Little Red Jet (Australia and the US), Luke runs the member portal, the data platform and the tools that turn a group of independent motels into one voice at the negotiating table.
Retro Motels Pty Ltd is a registered Australian company (ACN 700 480 011), based in Burleigh Heads, Queensland. The night window is always open — call Jeremy on 0477 764 448.
A],
            ['q' => 'What are the first priorities for Retro Motels?', 'a' => <<<'A'
Stage one is buying power. Our first group tenders are in market right now across four cost lines: electricity ★ insurance ★ IT stack ★ OTA commissions. Four lines with zero impact on your guest experience or your creative concept — just better economics.
Alongside the tenders, every member property gets included in our cost benchmarking: know exactly where you sit on energy per room night, commission rates, insurance per bed and tech spend versus independents across Australia. Chains have this information. Independents haven't — until now.
A],
            ['q' => 'My property isn\'t really "retro." Can I still join?', 'a' => <<<'A'
Yes. We are open to all independent accommodation and lodging providers — motels, guesthouses, B&Bs, cabins, apartments, farmstays, resorts.
Retro Motels has a brand path that will make sense in the future. But stage one — achieving a fair playing field and making a significant change to an industry — is open to every independent operator. If you negotiate your own power bill and pay your own OTA commission, this collective is for you.
A],
            ['q' => 'Is this a franchise? Will I lose my identity?', 'a' => <<<'A'
No, and never. Not a franchise ★ Not a booking site ★ Not a rate cartel.
You keep your name, your brand, your website, your soul. Guests never see us — we work behind the scenes on the cost lines, and you keep running the property only you can run. Same motel. Same owners. Same soul. Better economics.
A],
            ['q' => 'Will Retro Motels ever control my room rates?', 'a' => <<<'A'
No. You set your own rates and your own inventory, always. The collective never sets, coordinates or recommends room pricing between members — that's a hard line, and it's how a collective like ours stays on the right side of competition law. We negotiate what you pay; you decide what you charge.
A],
            ['q' => 'Is a collective like this even legal?', 'a' => <<<'A'
Yes. Small businesses in Australia are permitted to bargain collectively with suppliers under the ACCC's collective bargaining class exemption. The same legal pathway that lets farmers pool their bargaining power. It's not a loophole; it's exactly what the exemption was created for.
A],
            ['q' => 'What does membership cost?', 'a' => <<<'A'
One flat fee, scaled to the size of your property — so a little motel never subsidises a big one. No fee stacks. No cut of your profit. No per-booking fees. No commissions.
There are three tiers — Standard, Growth and Full Package — depending on how much you want taken off your plate. We publish the rate card for operators, not the internet: request the founder pack and we'll walk you through it in ten minutes.
A],
            ['q' => '"No fees until you\'re better off" — what does that actually mean?', 'a' => <<<'A'
Simple: if your savings don't outweigh our fee, you don't need to pay. Fees only apply once negotiated group deals are live and delivering your property a real, visible financial result. Until the collective is putting more money in your pocket than it costs, it costs you nothing. We win when you win — that's the whole model.
A],
            ['q' => 'Am I locked into a contract?', 'a' => <<<'A'
No lock-in, no minimum term, no exit penalties. Opt out any time with 30 days' notice. The only thing you give up when you leave is what you joined for — access to the group's negotiated rates and benchmarking. Your brand, your bookings and your business were always yours.
A],
            ['q' => 'Are the savings real yet, or is this still an idea?', 'a' => <<<'A'
Honest answer: the first tenders are being finalised now, and we'd rather tell you that straight than invent a case study. That's exactly why founding membership exists — the operators who register now get founding terms, shape the first deals, and are inside every tender we win from day one. The more rooms at the table, the bigger the number we all negotiate with. One motel is charming. A thousand create a movement.
Ready to look around? Registering at portal.retromotels.com takes about five minutes and costs nothing.
A],
        ];

        Setting::put('faq', $faq);
        $this->info('Seeded ' . count($faq) . ' FAQ entries.');
        return self::SUCCESS;
    }
}
