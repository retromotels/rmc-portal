<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentEvent;
use Illuminate\Http\Request;

/**
 * Member-facing SOP / template library. Members open a document (prefilled with
 * their property details), edit it in the browser and download a Word copy.
 * Opens and downloads are logged for head-office analytics.
 */
class DocumentController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.documents'), 404);
    }

    public function index()
    {
        $this->guard();

        return view('tools.documents.index', [
            'documents' => Document::where('is_published', true)->orderBy('sort')->orderBy('title')->get(),
        ]);
    }

    public function show(Document $document)
    {
        $this->guard();
        abort_unless($document->is_published, 404);

        $this->log($document, 'view');

        return view('tools.documents.edit', [
            'document' => $document,
            'content'  => $document->personalise($this->currentProperty()),
        ]);
    }

    /** Receive the edited HTML and return a Word-openable document. */
    public function download(Request $r, Document $document)
    {
        $this->guard();
        abort_unless($document->is_published, 404);

        $html = (string) $r->input('content', '');
        if (trim($html) === '') {
            $html = $document->personalise($this->currentProperty());
        }

        $this->log($document, 'download');

        $prop = $this->currentProperty();
        $filename = \Illuminate\Support\Str::slug(($prop->motel ?: 'my') . '-' . $document->title) . '.doc';

        $doc = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">'
            . '<head><meta charset="utf-8"><title>' . e($document->title) . '</title>'
            . '<style>body{font-family:Calibri,Arial,sans-serif;font-size:11pt;color:#222;line-height:1.5} h1{font-size:20pt} h2{font-size:14pt;margin-top:16pt} ol,ul{margin:0 0 10pt 22pt}</style>'
            . '</head><body>' . $html . '</body></html>';

        return response($doc, 200, [
            'Content-Type'        => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function log(Document $document, string $action): void
    {
        $prop = $this->currentProperty();
        DocumentEvent::create([
            'document_id'   => $document->id,
            'user_id'       => auth()->id(),
            'property_id'   => $prop->id ?? null,
            'property_name' => $prop->motel ?: $prop->name,
            'action'        => $action,
            'created_at'    => now(),
        ]);
    }
}
