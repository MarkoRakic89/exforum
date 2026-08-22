@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Rezervacije</h1>
<div class="d-flex justify-content-end mb-3">
    <form method="GET" action="{{ route('admin.reservations') }}" class="d-flex">
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
                    <th>Ponuda</th>
                    <th>Prodavac</th>
                    <th>Kupac</th>
                    <th>Iznos (€)</th>
                    <th>Status</th>
                    <th>Rezervisano</th>
                    <th>Potvrđeno</th>
                    <th>Završeno</th>
                    <th>Otkazano</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($reservations as $res)
                <tr>
                    <td>{{ $res->id }}</td>
                    <td>{{ $res->offer_id }}</td>
                    <td>{{ $res->seller->naziv }}</td>
                    <td>{{ $res->buyer->naziv }}</td>
                    <td>{{ number_format($res->amount_reserved_eur, 2) }}</td>
                    <td>{{ $res->state }}</td>
                    <td>{{ optional($res->reserved_at)->format('d.m.Y H:i') }}</td>
                    <td>{{ optional($res->confirmed_at)->format('d.m.Y H:i') }}</td>
                    <td>{{ optional($res->completed_at)->format('d.m.Y H:i') }}</td>
                    <td>{{ optional($res->canceled_at)->format('d.m.Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{ $reservations->links('admin.bootstrap-5') }}
@endsection