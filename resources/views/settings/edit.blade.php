@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Podešavanja profila</h1>
<form method="POST" action="{{ route('settings.update') }}" class="realtime-validation">
    @csrf
    @method('PUT')
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label" for="naziv">Naziv kompanije</label>
            @php
                $readonly = ! (method_exists($user, 'hasRole') && $user->hasRole('admin'));
            @endphp
            <input type="text" name="naziv" id="naziv" class="form-control" value="{{ old('naziv', $user->naziv) }}"
                {{ $readonly ? 'readonly' : '' }} required>
            @if($readonly)
                <small class="text-muted">Samo admin može izmeniti naziv kompanije.</small>
            @endif
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Email za notifikacije</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}"
                {{ $readonly ? 'readonly' : '' }} required>
            @if($readonly)
                <small class="text-muted">Samo admin može izmeniti email.</small>
            @endif
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label" for="grad_id">Grad</label>
            <select name="grad_id" id="grad_id" class="form-select" required {{ $readonly ? 'disabled' : '' }}>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ (old('grad_id', $user->grad_id) == $city->id) ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            @if($readonly)
                <small class="text-muted">Samo admin može izmeniti grad.</small>
            @endif
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <hr>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label" for="password">Nova lozinka (opciono)</label>
            <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
            <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Potvrdi lozinku</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Sačuvaj izmene</button>
</form>
@endsection