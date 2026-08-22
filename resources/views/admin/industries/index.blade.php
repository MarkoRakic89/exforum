@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Delatnosti</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.industries.create') }}" class="btn btn-primary">Dodaj delatnost</a>
    <form method="GET" action="{{ route('admin.industries.index') }}" class="d-flex">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm me-2" placeholder="Pretraga...">
        <button type="submit" class="btn btn-sm btn-outline-primary">Pretraži</button>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Naziv</th>
                <th>Šifra</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            @forelse($industries as $industry)
            <tr>
                <td>{{ $industry->id }}</td>
                <td>{{ $industry->name }}</td>
                <td>{{ $industry->code }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.industries.edit', $industry->id) }}" class="btn btn-sm btn-outline-primary">Izmeni</a>
                            <form method="POST" action="{{ route('admin.industries.destroy', $industry->id) }}" class="d-inline"
                                onsubmit="return confirm('Da li ste sigurni da želite obrisati ovu delatnost?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Obriši</button>
                            </form>
                        </div>
                    </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">Nema unetih delatnosti.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">
        {{ $industries->links('admin.bootstrap-5') }}
    </div>
</div>
@endsection