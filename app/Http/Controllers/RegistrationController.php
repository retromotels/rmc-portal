<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('registrations', 'uploads');

        // Property setup shows every section (A–H); A & B were captured at sign-up.
        $sections = collect(config('rmc.sections'))->map(fn ($s, $id) => $s + ['id' => $id]);

        return view('registration', [
            'user'     => $user,
            'sections' => $sections,
            'open'     => $request->query('open'),
        ]);
    }

    public function save(Request $request, string $section)
    {
        $cfg = config("rmc.sections.$section");
        abort_unless($cfg, 404);
        $user = $request->user();

        // Required non-file fields only (all uploads are optional).
        $rules = [];
        foreach ($cfg['fields'] as $f) {
            if (($f['req'] ?? false) && ($f['type'] ?? '') !== 'file') {
                $rules["fields.{$f['id']}"] = ['required'];
            }
        }
        $request->validate($rules);

        $input = $request->input('fields', []);
        $data = [];
        foreach ($cfg['fields'] as $f) {
            if (($f['type'] ?? '') === 'file') continue;
            if (array_key_exists($f['id'], $input)) $data[$f['id']] = $input[$f['id']];
        }

        Registration::updateOrCreate(
            ['user_id' => $user->id, 'section' => $section],
            ['data' => $data]
        );

        return redirect()->route('registration.index', ['open' => $section])
            ->with('status', $cfg['title'] . ' saved.');
    }

    public function upload(Request $request, string $section, string $field)
    {
        $cfg = config("rmc.sections.$section");
        abort_unless($cfg, 404);
        $user = $request->user();

        $request->validate([
            'file'   => ['required', 'array'],
            'file.*' => ['file', 'max:20480'], // 20 MB each
        ]);

        foreach ($request->file('file', []) as $file) {
            $path = $file->store("uploads/{$user->id}/{$section}", 'local');
            Upload::create([
                'user_id'       => $user->id,
                'section'       => $section,
                'field'         => $field,
                'original_name' => $file->getClientOriginalName(),
                'path'          => $path,
                'size'          => $file->getSize(),
                'mime'          => $file->getClientMimeType(),
            ]);
        }

        return redirect()->route('registration.index', ['open' => $section])
            ->with('status', 'File uploaded.');
    }

    public function download(Request $request, Upload $upload)
    {
        // Owners may only download their own files.
        abort_unless($upload->user_id === $request->user()->id, 403);
        return Storage::disk('local')->download($upload->path, $upload->original_name);
    }

    public function deleteFile(Request $request, Upload $upload)
    {
        abort_unless($upload->user_id === $request->user()->id, 403);
        Storage::disk('local')->delete($upload->path);
        $section = $upload->section;
        $upload->delete();
        return redirect()->route('registration.index', ['open' => $section])->with('status', 'File removed.');
    }
}
