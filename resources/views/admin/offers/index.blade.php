@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Ponude</h1>
<div class="d-flex justify-content-end mb-3">
    <form method="GET" action="{{ route('admin.offers') }}" class="d-flex">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm me-2"
            placeholder="Pretraga...">
        <button type="submit" class="btn btn-sm btn-outline-primary">Pretraži</button>
    </form>
</div>
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kompanija</th>
                    <th>Tip</th>
                    <th>Iznos (€)</th>
                    <th>Procenat (%)</th>
                    <th>Status</th>
                    <th>Kreirana</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($offers as $offer)
                <tr>
                    <td>{{ $offer->id }}</td>
                    <td>{{ $offer->user->naziv }}</td>
                    <td>{{ $offer->type === 'sell' ? 'Prodajem' : 'Kupujem' }}</td>
                    <td>{{ number_format($offer->amount_eur, 2) }}</td>
                    <td>{{ $offer->percent }}</td>
                    <td>{{ $offer->status }}</td>
                    <td>{{ $offer->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{ $offers->links('admin.bootstrap-5') }}
@endsection