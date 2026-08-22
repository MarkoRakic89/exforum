@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Gradovi</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.cities.create') }}" class="btn btn-primary">Dodaj grad</a>
    <form method="GET" action="{{ route('admin.cities.index') }}" class="d-flex">
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
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cities as $city)
            <tr>
                <td>{{ $city->id }}</td>
                <td>{{ $city->name }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.cities.edit', $city->id) }}" class="btn btn-sm btn-outline-primary">Izmeni</a>
                            <form method="POST" action="{{ route('admin.cities.destroy', $city->id) }}" class="d-inline"
                                onsubmit="return confirm('Da li ste sigurni da želite obrisati ovaj grad?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Obriši</button>
                            </form>
                        </div>
                    </td>
            </tr>
            @empty
            <tr>
                <td colspan="3">Nema unetih gradova.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">
        {{ $cities->links('admin.bootstrap-5') }}
    </div>
</div>
@endsection