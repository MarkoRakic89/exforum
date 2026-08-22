@extends('layouts.blankLayout')

@section('title', 'Reset lozinke')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
            <!-- Reset Password -->
            <div class="card p-sm-7 p-2">
                <div class="card-body mt-1">
                    <h4 class="mb-1">{{ __('Zaboravili ste lozinku?') }}</h4>
                    <p class="mb-5">Unesite vaš matični broj i poslaćemo vam novu lozinku na registrovani email.</p>
                    <form method="POST" action="{{ route('password.reset') }}">
                        @csrf
                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="text" class="form-control @error('maticni_broj') is-invalid @enderror" id="maticni_broj" name="maticni_broj" value="{{ old('maticni_broj') }}" placeholder="Matični broj" required autofocus>
                            <label for="maticni_broj">{{ __('Matični broj') }}</label>
                            @error('maticni_broj')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-5">
                            <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Pošalji novu lozinku') }}</button>
                        </div>
                    </form>
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="small">Nazad na prijavu</a>
                    </div>
                </div>
            </div>
            <!-- /Reset Password -->
            <img src="{{ asset('assets/json/img/illustrations/auth-basic-mask-light.png') }}" class="authentication-image d-none d-lg-block scaleX-n1-rtl" height="272" alt="triangle-bg" />
        </div>
    </div>
</div>
@endsection