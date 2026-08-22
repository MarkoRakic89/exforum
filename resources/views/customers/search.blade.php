@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Pretraga kupaca</h1>

<div class="card shadow border-0">
    <div class="card-header bg-tertiary text-white">
        <h4 class="mb-0"><i class="bi bi-search me-2"></i>Pretraga kupaca</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('offers.search') }}" method="GET" class="realtime-validation" novalidate>
            @csrf
            <div class="row g-4">
                {{-- BUDŽET --}}
                <div class="col-md-4">
                    <label for="amount_eur" class="form-label fw-semibold">Budžet u evrima (€)</label>
                    <input type="number" name="amount_eur" id="amount_eur" step="0.01" min="0.01"
                        class="form-control form-control-lg @error('amount_eur') is-invalid @enderror"
                        placeholder="npr. 1500" required>
                    <div class="invalid-feedback">@error('amount_eur') {{ $message }} @enderror</div>
                </div>

                {{-- GRADOVI --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-2">Izaberite gradove</label>
                    <div class="card border">
                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                            @foreach($cities as $city)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="cities[]"
                                    id="city_{{ $city->id }}" value="{{ $city->id }}">
                                <label class="form-check-label" for="city_{{ $city->id }}">
                                    {{ $city->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="text-danger small mt-1">@error('cities') {{ $message }} @enderror</div>
                </div>

                {{-- INDUSTRIJE --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-2">Izaberite industrije</label>
                    <div class="card border">
                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                            @foreach($industries as $industry)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="industries[]"
                                    id="industry_{{ $industry->id }}" value="{{ $industry->id }}">
                                <label class="form-check-label" for="industry_{{ $industry->id }}">
                                    {{ $industry->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="text-danger small mt-1">@error('industries') {{ $message }} @enderror</div>
                </div>
            </div>

            {{-- DUGME --}}
            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-lg btn-primary px-5 shadow-sm">
                    <i class="bi bi-search me-2"></i> Pretraži kupce
                </button>
            </div>
        </form>
    </div>
</div>

@endsection