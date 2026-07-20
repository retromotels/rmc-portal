@php
    $name = 'fields[' . $f['id'] . ']';
    $val = $data[$f['id']] ?? '';
    $req = ($f['req'] ?? false) ? ' <span style="color:var(--coral-d)">*</span>' : '';
@endphp
@if($f['type'] === 'textarea')
    <label class="fld"><span>{!! $f['label'] . $req !!}</span><textarea name="{{ $name }}" rows="2" placeholder="{{ $f['ph'] ?? '' }}">{{ $val }}</textarea></label>
@elseif($f['type'] === 'select')
    <label class="fld"><span>{!! $f['label'] . $req !!}</span>
        <select name="{{ $name }}"><option value="">—</option>
            @foreach($f['options'] as $o)<option @selected($val === $o)>{{ $o }}</option>@endforeach
        </select></label>
@elseif($f['type'] === 'yn')
    <label class="fld"><span>{!! $f['label'] . $req !!}</span>
        <select name="{{ $name }}"><option value="">—</option><option @selected($val === 'Yes')>Yes</option><option @selected($val === 'No')>No</option></select></label>
@else
    <label class="fld"><span>{!! $f['label'] . $req !!}</span>
        <input name="{{ $name }}" type="{{ $f['type'] === 'number' ? 'number' : 'text' }}" value="{{ $val }}" placeholder="{{ $f['ph'] ?? '' }}"></label>
@endif
