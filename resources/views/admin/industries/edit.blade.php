@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Izmena delatnosti</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.industries.update', $industry->id) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label" for="name">Naziv delatnosti</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $industry->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="code">Šifra (opciono)</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $industry->code) }}">
            </div>
            <button type="submit" class="btn btn-primary">Sačuvaj</button>
            <a href="{{ route('admin.industries.index') }}" class="btn btn-secondary">Nazad</a>
        </form>
    </div>
</div>
@endsection