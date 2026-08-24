<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierRequest;
use App\Models\SupplierSave;
use App\Services\Outbox;
use Illuminate\Http\Request;

/**
 * Member-facing supplier directory. Browse and filter curated supplier offers,
 * save favourites, grab a discount code, follow an offer link, or send a request
 * that emails head office to action.
 */
class SupplierController extends Controller
{
    private function guard(): void
    {
        abort_unless(config('rmc.features.suppliers'), 404);
    }

    public function index(Request $r)
    {
        $this->guard();

        $category = (string) $r->query('category');
        $saved    = $r->boolean('saved');

        $savedIds = SupplierSave::where('user_id', auth()->id())->pluck('supplier_id');

        $q = Supplier::where('is_active', true)->orderBy('sort')->orderBy('name');
        if (array_key_exists($category, config('rmc.supplier_categories'))) {
            $q->where('category', $category);
        }
        if ($saved) {
            $q->whereIn('id', $savedIds);
        }

        return view('tools.suppliers.index', [
            'suppliers' => $q->get(),
            'savedIds'  => $savedIds,
            'category'  => array_key_exists($category, config('rmc.supplier_categories')) ? $category : '',
            'saved'     => $saved,
            'counts'    => Supplier::where('is_active', true)->selectRaw('category, count(*) c')->groupBy('category')->pluck('c', 'category'),
        ]);
    }

    public function show(Supplier $supplier)
    {
        $this->guard();
        abort_unless($supplier->is_active, 404);

        return view('tools.suppliers.show', [
            'supplier' => $supplier,
            'isSaved'  => SupplierSave::where('user_id', auth()->id())->where('supplier_id', $supplier->id)->exists(),
        ]);
    }

    public function toggleSave(Supplier $supplier)
    {
        $this->guard();

        $existing = SupplierSave::where('user_id', auth()->id())->where('supplier_id', $supplier->id)->first();
        if ($existing) {
            $existing->delete();
            $msg = 'Removed from saved.';
        } else {
            SupplierSave::create(['supplier_id' => $supplier->id, 'user_id' => auth()->id()]);
            $msg = 'Saved.';
        }

        return back()->with('status', $msg);
    }

    public function sendRequest(Request $r, Supplier $supplier)
    {
        $this->guard();

        $data = $r->validate(['message' => ['nullable', 'string', 'max:2000']]);
        $prop = $this->currentProperty();

        $req = SupplierRequest::create([
            'supplier_id'   => $supplier->id,
            'user_id'       => auth()->id(),
            'property_id'   => $prop->id ?? null,
            'property_name' => $prop->motel ?: $prop->name,
            'contact_email' => $prop->email,
            'message'       => $data['message'] ?? null,
            'status'        => 'new',
        ]);

        $subject = 'Supplier request: ' . $supplier->name . ' — ' . ($prop->motel ?: $prop->name);
        $html = view('emails.supplier_request', ['supplier' => $supplier, 'req' => $req, 'prop' => $prop])->render();
        foreach ((array) config('rmc.admin_emails') as $adminEmail) {
            Outbox::queue('supplier_request', $adminEmail, 'RMC Admin', $subject, $html, [
                'supplier' => $supplier->name,
                'property' => $prop->motel ?: $prop->name,
            ]);
        }

        return back()->with('status', 'Request sent — head office will be in touch about ' . $supplier->name . '.');
    }
}
