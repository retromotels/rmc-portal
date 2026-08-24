@extends('layouts.admin')
@section('title', $supplier->exists ? 'Edit supplier' : 'New supplier')
@section('content')
<style>
  .fb{max-width:760px}
  .fb-back{color:#6c6577;text-decoration:none;font-size:13.5px}
  .fb-h{font-family:Oswald,sans-serif;font-size:24px;margin:8px 0 16px}
  .fb-card{background:var(--paper,#fff);border-radius:13px;padding:22px;box-shadow:var(--shadow,0 6px 20px rgba(0,0,0,.06))}
  .fld{display:block;margin-bottom:15px}
  .fld > span{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:#4a4453}
  .fld input,.fld select,.fld textarea{width:100%;padding:11px 13px;border:1.5px solid #e2d6c2;border-radius:9px;font:inherit;font-size:14.5px;background:#fff;box-sizing:border-box}
  .fld textarea{min-height:90px;resize:vertical}
  .row{display:flex;gap:14px;flex-wrap:wrap}.row .fld{flex:1;min-width:170px}
  .fb-err{background:#fbe4e4;color:#a4283a;border-radius:9px;padding:10px 12px;font-size:13px;margin-bottom:14px}
  .fb-save{background:#2e8b57;color:#fff;border:none;border-radius:9px;padding:12px 24px;font-weight:700;cursor:pointer;font-family:Oswald,sans-serif;letter-spacing:.5px;font-size:14px}
  .fb-del{background:none;border:1px solid #f0c2c8;color:#a4283a;border-radius:9px;padding:11px 16px;font-weight:700;cursor:pointer}
  .chk{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#4a4453}
  .grp{border:1px solid #efe4d2;border-radius:11px;padding:14px 16px;margin-bottom:15px;background:#fbf6ec}
  .grp h3{font-family:Oswald,sans-serif;font-size:14px;margin:0 0 10px;letter-spacing:.4px}
</style>

<div class="fb">
  <a class="fb-back" href="{{ route('admin.suppliers') }}">← Suppliers</a>
  <h1 class="fb-h">{{ $supplier->exists ? 'Edit supplier' : 'New supplier' }}</h1>

  <div class="fb-card">
    @if($errors->any())<div class="fb-err">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ $supplier->exists ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}">
      @csrf
      @if($supplier->exists)@method('PUT')@endif

      <div class="row">
        <label class="fld"><span>Supplier name</span><input type="text" name="name" value="{{ old('name', $supplier->name) }}" required></label>
        <label class="fld"><span>Category</span>
          <select name="category">
            <option value="">—</option>
            @foreach(config('rmc.supplier_categories') as $k => $lbl)
              <option value="{{ $k }}" @selected(old('category', $supplier->category) === $k)>{{ $lbl }}</option>
            @endforeach
          </select>
        </label>
      </div>
      <label class="fld"><span>One-line summary</span><input type="text" name="summary" value="{{ old('summary', $supplier->summary) }}" maxlength="200"></label>
      <label class="fld"><span>Full description</span><textarea name="description">{{ old('description', $supplier->description) }}</textarea></label>

      <div class="grp">
        <h3>The offer</h3>
        <div class="row">
          <label class="fld"><span>Offer type</span>
            <select name="offer_type" required>
              @foreach(config('rmc.supplier_offer_types') as $k => $lbl)
                <option value="{{ $k }}" @selected(old('offer_type', $supplier->offer_type) === $k)>{{ $lbl }}</option>
              @endforeach
            </select>
          </label>
          <label class="fld"><span>Offer headline</span><input type="text" name="offer_headline" value="{{ old('offer_headline', $supplier->offer_headline) }}" placeholder="e.g. 15% off first year"></label>
        </div>
        <div class="row">
          <label class="fld"><span>Discount code (for “Discount code” offers)</span><input type="text" name="discount_code" value="{{ old('discount_code', $supplier->discount_code) }}"></label>
        </div>
        <div class="row">
          <label class="fld"><span>Offer link URL (for “Offer link”)</span><input type="url" name="link_url" value="{{ old('link_url', $supplier->link_url) }}" placeholder="https://…"></label>
          <label class="fld"><span>Link button label</span><input type="text" name="link_label" value="{{ old('link_label', $supplier->link_label) }}" placeholder="e.g. Claim offer"></label>
        </div>
        <label class="fld"><span>Request contact email (for “Request” offers — defaults to admin inbox)</span><input type="email" name="contact_email" value="{{ old('contact_email', $supplier->contact_email) }}"></label>
      </div>

      <label class="fld"><span>Terms &amp; conditions</span><textarea name="terms">{{ old('terms', $supplier->terms) }}</textarea></label>
      <div class="row">
        <label class="fld"><span>Website</span><input type="url" name="website" value="{{ old('website', $supplier->website) }}" placeholder="https://…"></label>
        <label class="fld"><span>Sort order</span><input type="number" name="sort" value="{{ old('sort', $supplier->sort ?? 0) }}" min="0"></label>
      </div>
      <label class="chk" style="margin-bottom:16px"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true))> Active (visible to members)</label>

      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <button class="fb-save" type="submit">{{ $supplier->exists ? 'Save changes' : 'Add supplier' }}</button>
        @if($supplier->exists)
          <button class="fb-del" type="submit" form="del-form" onclick="return confirm('Delete this supplier?')" style="margin-left:auto">Delete</button>
        @endif
      </div>
    </form>
    @if($supplier->exists)
      <form id="del-form" method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}">@csrf @method('DELETE')</form>
    @endif
  </div>
</div>
@endsection
