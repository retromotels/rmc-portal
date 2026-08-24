<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierAdminController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.suppliers'), 404);
    }

    public function index()
    {
        $this->guard();

        return view('admin.suppliers.index', [
            'suppliers'   => Supplier::withCount('saves', 'requests')->orderBy('sort')->orderBy('name')->get(),
            'openRequests' => SupplierRequest::where('status', 'new')->count(),
        ]);
    }

    public function create()
    {
        $this->guard();
        return view('admin.suppliers.form', ['supplier' => new Supplier(['offer_type' => 'link', 'is_active' => true])]);
    }

    public function store(Request $r)
    {
        $this->guard();
        Supplier::create($this->validated($r));
        return redirect()->route('admin.suppliers')->with('status', 'Supplier added.');
    }

    public function edit(Supplier $supplier)
    {
        $this->guard();
        return view('admin.suppliers.form', ['supplier' => $supplier]);
    }

    public function update(Request $r, Supplier $supplier)
    {
        $this->guard();
        $supplier->update($this->validated($r));
        return redirect()->route('admin.suppliers')->with('status', 'Supplier saved.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->guard();
        $supplier->delete();
        return redirect()->route('admin.suppliers')->with('status', 'Supplier deleted.');
    }

    public function requests()
    {
        $this->guard();
        return view('admin.suppliers.requests', [
            'requests' => SupplierRequest::with('supplier')->latest()->paginate(50),
        ]);
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'name'           => ['required', 'string', 'max:160'],
            'category'       => ['nullable', Rule::in(array_keys(config('rmc.supplier_categories')))],
            'summary'        => ['nullable', 'string', 'max:200'],
            'description'    => ['nullable', 'string', 'max:4000'],
            'offer_type'     => ['required', Rule::in(array_keys(config('rmc.supplier_offer_types')))],
            'offer_headline' => ['nullable', 'string', 'max:120'],
            'discount_code'  => ['nullable', 'string', 'max:80'],
            'link_url'       => ['nullable', 'url', 'max:300'],
            'link_label'     => ['nullable', 'string', 'max:60'],
            'terms'          => ['nullable', 'string', 'max:2000'],
            'contact_email'  => ['nullable', 'email', 'max:190'],
            'website'        => ['nullable', 'url', 'max:200'],
            'sort'           => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'      => ['nullable', 'boolean'],
        ]) + ['is_active' => $r->boolean('is_active')];
    }
}
