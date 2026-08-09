<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\HealthRequest;
use App\Services\AuditService;
use App\Services\Notify;
use App\Services\Outbox;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function index(Request $request, AuditService $audit)
    {
        $property = $this->currentProperty();
        $result = null;

        if ($request->filled('url')) {
            $request->validate(['url' => ['required', 'string', 'max:255']]);
            $result = $audit->run($request->input('url'));

            // Notify head office once per property (until they clear it) so they can follow up.
            $exists = AdminNotification::where('type', 'website_check')
                ->where('user_id', $property->id)->whereNull('read_at')->exists();
            if (!$exists) {
                Notify::admin(
                    'website_check',
                    ($property->motel ?: $property->name) . ' ran a website health check',
                    'Score ' . ($result['overall'] ?? '?') . '/100 for ' . $request->input('url'),
                    $property
                );
            }
        }

        return view('health', [
            'result'    => $result,
            'default'   => $property->sectionData('A')['website'] ?? '',
            'requested' => HealthRequest::where('user_id', $property->id)->where('status', 'requested')->pluck('type')->all(),
        ]);
    }

    public function request(Request $request, string $type)
    {
        $cfg = config("rmc.health_requests.$type");
        abort_unless($cfg, 404);

        $property = $this->currentProperty();

        $already = HealthRequest::where('user_id', $property->id)
            ->where('type', $type)->where('status', 'requested')->exists();

        if (!$already) {
            HealthRequest::create(['user_id' => $property->id, 'type' => $type, 'status' => 'requested']);
            Notify::admin('health_request', ($property->motel ?: $property->name) . ' requested: ' . $cfg['label'], null, $property);
            Outbox::healthRequest($property, $cfg['label']);
        }

        return back()->with('status', 'Thanks — your ' . $cfg['label'] . ' request is in. Our team will be in touch shortly.');
    }
}
