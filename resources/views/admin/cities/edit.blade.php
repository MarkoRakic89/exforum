@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Izmena grada</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cities.update', $city->id) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label" for="name">Naziv grada</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $city->name) }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Sačuvaj</button>
            <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">Nazad</a>
        </form>
    </div>
</div>
@endsection