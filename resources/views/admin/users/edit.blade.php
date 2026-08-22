@extends('layouts.contentNavbarLayout')

@section('content')
<h2>Izmeni kompaniju</h2>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Matični broj</label>
                        <input type="text" class="form-control" value="{{ $user->maticni_broj }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="naziv">Naziv kompanije</label>
                        <input type="text" name="naziv" id="naziv" class="form-control"
                            value="{{ old('naziv', $user->naziv) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email (za notifikacije)</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="{{ old('email', $user->email) }}"
                            pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
                            title="Unesite validnu email adresu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="grad_id">Sedište</label>
                        <select name="grad_id" id="grad_id" class="form-select" required>
                            @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('grad_id', $user->grad_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="industry_id" class="form-label">Delatnost</label>
                        <select name="industry_id" id="industry_id" class="form-select" required>
                            <option value="">-- Odaberite Delatnost --</option>
                            @foreach($industries as $industy)
                            <option value="{{ $industy->id }}" {{ old('industry_id', $user->industry_id) == $industy->id ? 'selected' : '' }}>{{ $industy->code }} - {{ $industy->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status naloga</label>
                        <select name="status" id="status" class="form-select" required>
                            @foreach(['active' => 'Aktivan', 'inactive' => 'Neaktivan', 'locked' => 'Zaključan'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $user->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Nova lozinka (opciono)</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <small class="text-muted">Ostavite prazno ako ne želite menjati lozinku.</small>
                    </div>

                    @if($user->avatar)
                    <p><strong>Avatar:</strong><br>
                        <img src="{{ asset($user->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 80px; height: 80px;">
                    </p>
                    @endif
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar (slika)</label>
                        <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') is-invalid @enderror">
                        @error('avatar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Opis kompanije</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $user->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">Sačuvaj izmene</button>
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Nazad</a>
                        <form method="POST" action="{{ route('admin.users.resetPassword', $user->id) }}" onsubmit="return confirm('Generisati novu lozinku za kompaniju?');">
                            @csrf
                            <button type="submit" class="btn btn-warning">Resetuj lozinku</button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection