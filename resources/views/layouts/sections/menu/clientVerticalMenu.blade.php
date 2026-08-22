@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
@endphp

<!--
  Custom vertical menu for clients (non‑admin users).
  This menu is based on the Materio vertical menu but only contains
  entries relevant to regular users. Admin specific links are
  intentionally omitted. When the user is authenticated and is not
  an admin, this file will be included instead of the default
  verticalMenu via a conditional check in contentNavbarLayout.
-->
<aside id="layout-menu" class="layout-menu menu-vertical menu">
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo admin demo me-1"><img src="{{ asset('assets/json/img/logo-login.png') }}"
                        class="logo w-100"/></span>
      <span class="app-brand-text demo menu-text fw-semibold ms-2">{{ config('variables.templateName') }}</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="menu-toggle-icon d-xl-inline-block align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <li class="menu-header mt-4"><span class="menu-header-text">Platform</span></li>

    <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon icon-base ri ri-home-3-line"></i>
        <div>Dashboard</div>
      </a>
    </li>

    {{-- Unified offers and reservations link --}}
    <li class="menu-item {{ Route::is('profile') ? 'active' : '' }}">
      <a href="{{ route('profile') }}#my-offers" class="menu-link">
        <i class="menu-icon icon-base ri ri-briefcase-4-line"></i>
        <div>Moje ponude i rezervacije</div>
      </a>
    </li>

    {{-- Podesavanja removed from sidebar; password change is now accessible via user dropdown in the navbar --}}

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
  </ul>
</aside>