@extends('layouts.contentNavbarLayout')

@section('content')
<h1 class="mb-3">Kompanije</h1>
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Dodaj kompaniju</a>
    <form method="GET" action="{{ route('admin.users') }}" class="d-flex">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm me-2" placeholder="Pretraga...">
        <button type="submit" class="btn btn-sm btn-outline-primary">Pretraži</button>
    </form>
</div>
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Matični broj</th>
                    <th>Naziv kompanije</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Ocena</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->maticni_broj }}</td>
                    <td>{{ $user->naziv }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->status }}</td>
                    <td>{{ number_format($user->avg_rating, 2) }} ({{ $user->ratings_count }})</td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if($user->status !== 'locked')
                                <form method="POST" action="{{ route('admin.users.lock', $user->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Zaključaј</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.unlock', $user->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Otključaј</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">Izmeni</a>
                            <form method="POST" action="{{ route('admin.users.resetPassword', $user->id) }}" class="d-inline" onsubmit="return confirm('Generisati novu lozinku?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">Reset lozinke</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{ $users->links('admin.bootstrap-5') }}
@endsection