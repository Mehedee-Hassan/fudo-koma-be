@extends('admin.layout')
@section('title',ucwords(str_replace('-',' ',$resource)))
@section('content')
<div class="page-heading"><div><a class="muted" href="/admin/{{ $resource }}">← Back to {{ str_replace('-',' ',$resource) }}</a><h1>{{ $record->exists?'Edit':'Create' }} record</h1></div></div>
<form class="panel form-panel" method="post" action="/admin/{{ $resource }}{{ $record->exists?'/'.$record->id:'' }}">@csrf @if($record->exists) @method('PUT') @endif
<div class="form-grid">@foreach($fields as $field=>$rules)<label>{{ ucwords(str_replace('_',' ',$field)) }}
@if(preg_match('/(?:^|\|)in:([^|]+)/',$rules,$matches))<select name="{{ $field }}">@foreach(explode(',',$matches[1]) as $option)<option value="{{ $option }}" @selected(old($field,$record->$field)===$option)>{{ ucfirst($option) }}</option>@endforeach</select>
@elseif(isset($choices[$field]))<select name="{{ $field }}" required><option value="">Choose a {{ str_contains($field,'cart')?'cart':'person' }}</option>@foreach($choices[$field] as $choice)<option value="{{ $choice->id }}" @selected(old($field,$record->$field)==$choice->id)>#{{ $choice->id }} · {{ $choice->name }}</option>@endforeach</select>
@elseif(str_contains($rules,'boolean'))<select name="{{ $field }}"><option value="1" @selected(old($field,$record->$field??1)==1)>Enabled</option><option value="0" @selected(old($field,$record->$field??1)==0)>Disabled</option></select>
@elseif(in_array($field,['description','body','resolution_note']))<textarea name="{{ $field }}" rows="5">{{ old($field,$record->$field) }}</textarea>
@else<input name="{{ $field }}" type="{{ $field==='password'?'password':($field==='email'?'email':(str_contains($field,'_at') && str_contains($rules,'H:i')?'time':($field==='specific_date'?'date':'text'))) }}" value="{{ $field==='password'?'':old($field, str_contains($rules,'H:i')?substr($record->$field??'',0,5):$record->$field) }}" {{ str_contains($rules,'required')?'required':'' }} @if($field==='password') autocomplete="new-password" @endif>@endif
@if($field==='password' && $record->exists)<small>Leave blank to keep the current password.</small>@endif
</label>@endforeach</div><div class="form-footer"><a href="/admin/{{ $resource }}">Cancel</a><button class="button">Save changes →</button></div></form>
@if($resource==='carts' && $record->exists)<form class="panel form-panel" method="post" action="/admin/carts/{{ $record->id }}/photos" enctype="multipart/form-data">@csrf<h2>Cart photos</h2><p class="muted">JPEG, PNG or WebP. Up to 5 MB each, maximum 10 photos.</p><div class="photo-grid">@foreach($record->photos as $photo)<img src="{{ $photo->url }}" alt="Photo of {{ $record->name }}">@endforeach</div><label>Choose photo<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label><button class="button">Upload photo</button></form>@endif
@endsection
