@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Dodaj novu delatnost</h1>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.industries.store') }}" class="realtime-validation">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Naziv delatnosti</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">Šifra delatnosti (opciono)</label>
                        <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Sačuvaj</button>
                    <a href="{{ route('admin.industries.index') }}" class="btn btn-link">Nazad</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection