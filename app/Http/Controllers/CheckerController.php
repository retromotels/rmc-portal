<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;

class CheckerController extends Controller
{
    public function index(Request $request, AuditService $audit)
    {
        $result = null;
        if ($request->filled('url')) {
            $request->validate(['url' => ['required', 'string', 'max:255']]);
            $result = $audit->run($request->input('url'));
        }

        return view('checker', [
            'result'  => $result,
            'default' => auth()->user()->sectionData('A')['website'] ?? '',
        ]);
    }
}
