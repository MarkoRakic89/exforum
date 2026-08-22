@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Pregled</h1>
<p>Dobrodošli, {{ Auth::user()->naziv ?? '' }}!</p>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Sve ponude</h5>
                <p class="display-6 fw-bold mb-0">{{ $totalOffers }}</p>
                <small class="text-muted">Ukupan broj kreiranih ponuda</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Aktivne ponude</h5>
                <p class="display-6 fw-bold mb-0">{{ $activeOffers }}</p>
                <small class="text-muted">Ponude koje su otvorene ili u toku</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Završene ponude</h5>
                <p class="display-6 fw-bold mb-0">{{ $completedOffers }}</p>
                <small class="text-muted">Ponude koje su uspešno realizovane</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Rezervacije (prodaja)</h5>
                <p class="display-6 fw-bold mb-0">{{ $sellingReservations }}</p>
                <small class="text-muted">Aktivne rezervacije gde ste prodavac</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Rezervacije (kupovina)</h5>
                <p class="display-6 fw-bold mb-0">{{ $buyingReservations }}</p>
                <small class="text-muted">Aktivne rezervacije gde ste kupac</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Nepročitane notifikacije</h5>
                <p class="display-6 fw-bold mb-0">{{ $unreadNotifications }}</p>
                <small class="text-muted">Kliknite na ikonicu zvona za detalje</small>
            </div>
        </div>
    </div>
</div>
<div class="mb-3">
    <a href="{{ route('offers.create') }}" class="btn btn-success me-2">Nova ponuda</a>
</div>
{{-- The search form previously embedded on the dashboard has been moved to its own page. --}}

@endsection