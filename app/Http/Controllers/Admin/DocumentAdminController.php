<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentEvent;
use Illuminate\Http\Request;

class DocumentAdminController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.documents'), 404);
    }

    public function index()
    {
        $this->guard();

        $views = DocumentEvent::where('action', 'view')->selectRaw('document_id, count(*) c')->groupBy('document_id')->pluck('c', 'document_id');
        $downloads = DocumentEvent::where('action', 'download')->selectRaw('document_id, count(*) c')->groupBy('document_id')->pluck('c', 'document_id');

        return view('admin.documents.index', [
            'documents' => Document::orderBy('sort')->orderBy('title')->get(),
            'views'     => $views,
            'downloads' => $downloads,
        ]);
    }

    public function create()
    {
        $this->guard();
        return view('admin.documents.form', ['document' => new Document(['is_published' => true])]);
    }

    public function store(Request $r)
    {
        $this->guard();
        Document::create($this->validated($r));
        return redirect()->route('admin.documents')->with('status', 'Document created.');
    }

    public function edit(Document $document)
    {
        $this->guard();
        return view('admin.documents.form', ['document' => $document]);
    }

    public function update(Request $r, Document $document)
    {
        $this->guard();
        $document->update($this->validated($r));
        return redirect()->route('admin.documents')->with('status', 'Document saved.');
    }

    public function destroy(Document $document)
    {
        $this->guard();
        $document->delete();
        return redirect()->route('admin.documents')->with('status', 'Document deleted.');
    }

    /** Usage detail: which properties viewed / downloaded, and when. */
    public function stats(Document $document)
    {
        $this->guard();
        $document->load(['events' => fn ($e) => $e->latest('created_at')->limit(500)]);

        $byProp = $document->events->groupBy('property_id')->map(function ($rows) {
            return [
                'name'      => $rows->first()->property_name ?: 'Unknown property',
                'views'     => $rows->where('action', 'view')->count(),
                'downloads' => $rows->where('action', 'download')->count(),
                'last'      => $rows->max('created_at'),
            ];
        })->sortByDesc('downloads')->values();

        return view('admin.documents.stats', ['document' => $document, 'byProp' => $byProp]);
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'title'        => ['required', 'string', 'max:160'],
            'category'     => ['nullable', 'string', 'max:60'],
            'description'  => ['nullable', 'string', 'max:400'],
            'body'         => ['required', 'string'],
            'sort'         => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['nullable', 'boolean'],
        ]) + ['is_published' => $r->boolean('is_published')];
    }
}
