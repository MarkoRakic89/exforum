@extends('layouts.contentNavbarLayout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Podešavanja platforme</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="realtime-validation">
                    @csrf
                    <div class="mb-3">
                        <label for="max_percent" class="form-label">Maksimalni procenat (%)</label>
                        <input type="number" step="0.01" name="max_percent" id="max_percent" class="form-control" data-min="0" data-max="100" value="{{ old('max_percent', $maxPercent) }}" required>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Procenat ne može prelaziti 100%</small>
                    </div>
                    <div class="mb-3">
                        <label for="max_amount" class="form-label">Maksimalni iznos (€)</label>
                        <input type="number" step="0.01" name="max_amount" id="max_amount" class="form-control" data-min="0" value="{{ old('max_amount', $maxAmount) }}" required>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Unesite 0 za neograničeno</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Sačuvaj</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection