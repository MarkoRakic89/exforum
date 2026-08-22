<?php

use Illuminate\Support\Facades\Route;

// test addition removed by integration of platforma routes

/* Additional Platforma routes for business logic */
use App\Http\Controllers\AuthController as PlatformAuthController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminCityController;
use App\Http\Controllers\AdminIndustryController;
use App\Http\Controllers\PasswordResetController;

// Authentication routes
Route::get('/login', [PlatformAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [PlatformAuthController::class, 'login']);
Route::post('/logout', [PlatformAuthController::class, 'logout'])->name('logout');

// Password reset request routes (no authentication required)
Route::get('/password/reset', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.reset');

// Protected routes requiring authentication
Route::middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile route
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

    // Update profile (avatar and description)
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings routes
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Export CSV for offers and reservations
    Route::get('/profile/export/offers', [ProfileController::class, 'exportOffers'])->name('profile.export.offers');
    Route::get('/profile/export/reservations', [ProfileController::class, 'exportReservations'])->name('profile.export.reservations');

    // Offer routes
    Route::get('/customers/search', [OfferController::class, 'searchForm'])->name('customers.search');
    Route::get('/offers/create', [OfferController::class, 'create'])->name('offers.create');
    Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
    Route::get('/offers/search', [OfferController::class, 'search'])->name('offers.search');
    Route::post('/offers/{id}/clone', [OfferController::class, 'clone'])->name('offers.clone');

    // AJAX endpoint to refresh buyer matches for an offer
    Route::get('/offers/{id}/matches', [OfferController::class, 'matches'])->name('offers.matches');
    // Edit/update/deactivate offer
    Route::get('/offers/{id}/edit', [OfferController::class, 'edit'])->name('offers.edit');
    Route::put('/offers/{id}', [OfferController::class, 'update'])->name('offers.update');
    Route::delete('/offers/{id}', [OfferController::class, 'destroy'])->name('offers.destroy');

    // Reservation routes
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{id}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Message routes
    Route::get('/reservations/{id}/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/reservations/{id}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/reservations/{id}/messages/modal', [MessageController::class, 'modal'])
        ->name('messages.modal');
    // Rating routes
    Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');
});

// Admin routes (restricted to authenticated users; role-based access control can be added later if needed)
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/feature', [AdminController::class, 'featureUser'])->name('admin.users.feature');
    Route::post('/users/{id}/lock', [AdminController::class, 'lockUser'])->name('admin.users.lock');
    Route::post('/users/{id}/unlock', [AdminController::class, 'unlockUser'])->name('admin.users.unlock');
    Route::get('/offers', [AdminController::class, 'offers'])->name('admin.offers');
    Route::get('/reservations', [AdminController::class, 'reservations'])->name('admin.reservations');
    Route::get('/messages', [AdminController::class, 'messages'])->name('admin.messages');
    Route::get('/ratings', [AdminController::class, 'ratings'])->name('admin.ratings');

    // Settings management
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');

    // User management
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{id}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.resetPassword');

    // Gradovi (Cities) management
    Route::get('/cities', [AdminCityController::class, 'index'])->name('admin.cities.index');
    Route::get('/cities/create', [AdminCityController::class, 'create'])->name('admin.cities.create');
    Route::post('/cities', [AdminCityController::class, 'store'])->name('admin.cities.store');
    Route::get('/cities/{id}/edit', [AdminCityController::class, 'edit'])->name('admin.cities.edit');
    Route::put('/cities/{id}', [AdminCityController::class, 'update'])->name('admin.cities.update');
    Route::delete('/cities/{id}', [AdminCityController::class, 'destroy'])->name('admin.cities.destroy');

    // Delatnosti (Industries) management
    Route::get('/industries', [AdminIndustryController::class, 'index'])->name('admin.industries.index');
    Route::get('/industries/create', [AdminIndustryController::class, 'create'])->name('admin.industries.create');
    Route::post('/industries', [AdminIndustryController::class, 'store'])->name('admin.industries.store');
    Route::get('/industries/{id}/edit', [AdminIndustryController::class, 'edit'])->name('admin.industries.edit');
    Route::put('/industries/{id}', [AdminIndustryController::class, 'update'])->name('admin.industries.update');
    Route::delete('/industries/{id}', [AdminIndustryController::class, 'destroy'])->name('admin.industries.destroy');
});

// Email verification notice route to avoid RouteNotFoundException
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.verify');
