@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu">
  <div class="app-brand demo">
    @php
      $isAdminBrand = Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('admin');
    @endphp
    <a href="{{ $isAdminBrand ? route('admin.dashboard') : url('/') }}" class="app-brand-link">
      <span class="app-brand-logo admin demo me-1"><img src="{{ asset('assets/json/img/logo-login.png') }}"
                        class="logo w-100" /></span>
      <span class="app-brand-text demo menu-text fw-semibold ms-2">{{ config('variables.templateName') }}</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="menu-toggle-icon d-xl-inline-block align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    {{-- Provera da li je korisnik ulogovan i ima admin rolu --}}
    @php
      $isAdmin = Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('admin');
    @endphp


    {{-- ADMIN MENI --}}
    @if($isAdmin)
      <li class="menu-header mt-4"><span class="menu-header-text">Admin</span></li>

      <li class="menu-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
        <a href="{{ route('admin.dashboard') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-dashboard-line"></i>
          <div>Admin Dashboard</div>
        </a>
      </li>

      <li class="menu-item {{ Route::is('admin.users*') ? 'active' : '' }}">
        <a href="{{ route('admin.users') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-group-line"></i>
          <div>Kompanije</div>
        </a>
      </li>

      {{-- Gradovi --}}
      <li class="menu-item {{ Route::is('admin.cities*') ? 'active' : '' }}">
        <a href="{{ route('admin.cities.index') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-map-pin-line"></i>
          <div>Gradovi</div>
        </a>
      </li>

      {{-- Delatnosti --}}
      <li class="menu-item {{ Route::is('admin.industries*') ? 'active' : '' }}">
        <a href="{{ route('admin.industries.index') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-briefcase-line"></i>
          <div>Delatnosti</div>
        </a>
      </li>

      <li class="menu-item {{ Route::is('admin.offers*') ? 'active' : '' }}">
        <a href="{{ route('admin.offers') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-file-list-line"></i>
          <div>Ponude</div>
        </a>
      </li>

      <li class="menu-item {{ Route::is('admin.reservations*') ? 'active' : '' }}">
        <a href="{{ route('admin.reservations') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-calendar-check-line"></i>
          <div>Rezervacije</div>
        </a>
      </li>

      <li class="menu-item {{ Route::is('admin.messages*') ? 'active' : '' }}">
        <a href="{{ route('admin.messages') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-mail-line"></i>
          <div>Poruke</div>
        </a>
      </li>

      <li class="menu-item {{ Route::is('admin.ratings*') ? 'active' : '' }}">
        <a href="{{ route('admin.ratings') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-star-line"></i>
          <div>Ocene</div>
        </a>
      </li>

      <li class="menu-item {{ Route::is('admin.settings*') ? 'active' : '' }}">
        <a href="{{ route('admin.settings') }}" class="menu-link">
          <i class="menu-icon icon-base ri ri-tools-line"></i>
          <div>Podešavanja sistema</div>
        </a>
      </li>
       @else
    {{-- Zajedničke stavke za sve --}}
    <li class="menu-header mt-4"><span class="menu-header-text">Platform</span></li>

    <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon icon-base ri ri-home-3-line"></i>
        <div>Dashboard</div>
      </a>
    </li>

    <li class="menu-item {{ Route::is('offers.create') ? 'active' : '' }}">
      <a href="{{ route('offers.create') }}" class="menu-link">
        <i class="menu-icon icon-base ri ri-add-box-line"></i>
        <div>Kreiraj ponudu</div>
      </a>
    </li>

    <li class="menu-item {{ Route::is('offers.search') ? 'active' : '' }}">
      <a href="{{ route('offers.search') }}" class="menu-link">
        <i class="menu-icon icon-base ri ri-search-line"></i>
        <div>Pretraga ponuda</div>
      </a>
    </li>

    <li class="menu-item {{ Route::is('settings') ? 'active' : '' }}">
      <a href="{{ route('settings.edit') }}" class="menu-link">
        <i class="menu-icon icon-base ri ri-settings-3-line"></i>
        <div>Podešavanja</div>
      </a>
    </li>

    <li class="menu-item {{ Route::is('notifications.index') ? 'active' : '' }}">
      <a href="{{ route('notifications.index') }}" class="menu-link d-flex align-items-center">
        <i class="menu-icon icon-base ri ri-notification-2-line"></i>
        @php
            $unreadCount = auth()->check() ? auth()->user()->unreadNotifications->count() : 0;
        @endphp
        <div class="d-flex align-items-center">
          <span>Notifikacije</span>
          @if($unreadCount > 0)
            <span class="badge bg-danger rounded-pill ms-2">{{ $unreadCount }}</span>
          @endif
        </div>
      </a>
    </li>
    @endif

  </ul>
</aside>
