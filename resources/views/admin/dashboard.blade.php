@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Admin Dashboard</h1>
<p>Dobrodošli, {{ Auth::user()->naziv ?? '' }}!</p>

<div class="row g-3 mb-4">
    <!-- Users statistics -->
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Registrovane kompanije</h5>
                <p class="display-6 fw-bold mb-0">{{ $totalUsers }}</p>
                <small class="text-muted">Ukupan broj kompanija</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Aktivne kompanije</h5>
                <p class="display-6 fw-bold mb-0">{{ $activeUsers }}</p>
                <small class="text-muted">Kompanije sa aktivnim statusom</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Neaktivne kompanije</h5>
                <p class="display-6 fw-bold mb-0">{{ $inactiveUsers }}</p>
                <small class="text-muted">Nalog je deaktiviran</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Zaključane kompanije</h5>
                <p class="display-6 fw-bold mb-0">{{ $lockedUsers }}</p>
                <small class="text-muted">Nalog je zaključan</small>
            </div>
        </div>
    </div>

    <!-- Offer statistics -->
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Sve ponude</h5>
                <p class="display-6 fw-bold mb-0">{{ $totalOffers }}</p>
                <small class="text-muted">Ukupan broj ponuda</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Aktivne ponude</h5>
                <p class="display-6 fw-bold mb-0">{{ $activeOffers }}</p>
                <small class="text-muted">Objavljene i rezervisane ponude</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Završene ponude</h5>
                <p class="display-6 fw-bold mb-0">{{ $completedOffers }}</p>
                <small class="text-muted">Uspešno realizovane ponude</small>
            </div>
        </div>
    </div>

    <!-- Reservation statistics -->
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Sve rezervacije</h5>
                <p class="display-6 fw-bold mb-0">{{ $totalReservations }}</p>
                <small class="text-muted">Ukupan broj rezervacija</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Aktivne rezervacije</h5>
                <p class="display-6 fw-bold mb-0">{{ $activeReservations }}</p>
                <small class="text-muted">Rezervacije koje nisu otkazane</small>
            </div>
        </div>
    </div>

    <!-- Messages and Ratings -->
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Poruke</h5>
                <p class="display-6 fw-bold mb-0">{{ $totalMessages }}</p>
                <small class="text-muted">Ukupan broj poslatih poruka</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4 col-xl-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Ocene</h5>
                <p class="display-6 fw-bold mb-0">{{ $totalRatings }}</p>
                <small class="text-muted">Ukupan broj ocena</small>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <h4>Brze akcije</h4>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.users') }}" class="btn btn-primary">Upravljanje kompanijama</a>
        <a href="{{ route('admin.offers') }}" class="btn btn-primary">Upravljanje ponudama</a>
        <a href="{{ route('admin.reservations') }}" class="btn btn-primary">Upravljanje rezervacijama</a>
        <a href="{{ route('admin.messages') }}" class="btn btn-primary">Upravljanje porukama</a>
        <a href="{{ route('admin.ratings') }}" class="btn btn-primary">Upravljanje ocenama</a>
        <a href="{{ route('admin.settings') }}" class="btn btn-primary">Podešavanja</a>
    </div>
</div>
@endsection