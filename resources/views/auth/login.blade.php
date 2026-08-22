@extends('layouts.blankLayout')

@section('title', 'Login')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
            <!-- Login -->
            <div class="card p-sm-7 p-2">
                <!-- Logo -->
                <div class="app-brand justify-content-center">
                    <a href="{{ url('/') }}" class="app-brand-link gap-3">
                        <span class="app-brand-logo demo"><img src="{{ asset('assets/json/img/logo-login.png') }}"
                                class="logo w-100" /></span>
                    </a>
                </div>
                <!-- /Logo -->

                <div class="card-body mt-1">
                    <h4 class="mb-1">{{ __('Dobrodošli!') }}</h4>
                    <p class="mb-5">{{ __('Ulogujte se da biste nastavili.') }}</p>
                    @if(session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="text" class="form-control @error('maticni_broj') is-invalid @enderror"
                                id="maticni_broj" name="maticni_broj" value="{{ old('maticni_broj') }}"
                                placeholder="{{ __('Enter your ID number') }}" required autofocus>
                            <label for="maticni_broj">{{ __('Matični broj') }}</label>
                            @error('maticni_broj')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-5">
                            <div class="form-password-toggle form-control-validation">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password"
                                            class="form-control @error('password') is-invalid @enderror" name="password"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            required>
                                        <label for="password">{{ __('Password') }}</label>
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-5 pb-2 d-flex justify-content-between pt-2 align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember-me" {{
                                    old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember-me">{{ __('Zapamti me') }}</label>
                            </div>
                            <div>
                                <a href="{{ route('password.request') }}" class="small">{{ __('Zaboravili ste lozinku?') }}</a>
                            </div>
                        </div>
                        <div class="mb-5">
                            <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Potvrdi') }}</button>
                        </div>
                    </form>

                    <!-- Registration link intentionally omitted as user registration is handled elsewhere -->
                </div>
            </div>
            <!-- /Login -->
            <img src="{{ asset('assets/json/img/illustrations/auth-basic-mask-light.png') }}"
                class="authentication-image d-none d-lg-block scaleX-n1-rtl" height="272" alt="triangle-bg" />
        </div>
    </div>
</div>
@endsection