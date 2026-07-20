<?php

namespace App\Services;

use App\Models\PolicyDocument;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PolicyService
{
    /**
     * Generate the three signed policy PDFs for a user, store them on the
     * private disk and record them in policy_documents. Called at sign-up.
     */
    public function generateForUser(User $user): void
    {
        foreach (config('rmc.policies') as $type => $policy) {
            $acceptedAt = now();

            $pdf = Pdf::loadView('pdf.policy', [
                'user'       => $user,
                'policy'     => $policy,
                'acceptedAt' => $acceptedAt,
            ]);

            $path = "policies/{$user->id}/{$type}.pdf";
            Storage::disk('local')->put($path, $pdf->output());

            PolicyDocument::updateOrCreate(
                ['user_id' => $user->id, 'type' => $type],
                [
                    'title'         => $policy['title'],
                    'accepted_name' => $user->name,
                    'path'          => $path,
                    'accepted_at'   => $acceptedAt,
                ]
            );
        }
    }
}
