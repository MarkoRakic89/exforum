@extends('layouts.contentNavbarLayout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Dodaj novu kompaniju</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}" class="realtime-validation" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="maticni_broj" class="form-label">Matični broj</label>
                        <input type="text" name="maticni_broj" id="maticni_broj" class="form-control"
                            value="{{ old('maticni_broj') }}" pattern="\d{8}"
                            title="Matični broj mora sadržati tačno 8 cifara" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="naziv" class="form-label">Naziv kompanije</label>
                        <input type="text" name="naziv" id="naziv" class="form-control" value="{{ old('naziv') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (za notifikacije)</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="{{ old('email') }}"
                            pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
                            title="Unesite validnu email adresu" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="grad_id" class="form-label">Sedište</label>
                        <select name="grad_id" id="grad_id" class="form-select" required>
                            <option value="">-- Odaberite sedište --</option>
                            @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('grad_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="industry_id" class="form-label">Delatnost</label>
                        <select name="industry_id" id="industry_id" class="form-select" required>
                            <option value="">-- Odaberite Delatnost --</option>
                            @foreach($industries as $industy)
                            <option value="{{ $industy->id }}" {{ old('industry_id') == $industy->id ? 'selected' : '' }}>{{ $industy->code }} - {{ $industy->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar (slika)</label>
                        <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') is-invalid @enderror">
                        @error('avatar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Opis kompanije</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3"></textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Kreiraj kompaniju</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection