<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Offer;
use App\Models\Reservation;
use App\Models\Message;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        // Firme (korisnici)
        $totalUsers      = User::count();
        $activeUsers     = User::where('status', 'active')->count();
        $inactiveUsers   = User::where('status', 'inactive')->count();
        $lockedUsers     = User::where('status', 'locked')->count();

        // Ponude
        $totalOffers     = Offer::count();
        $activeOffers    = Offer::whereIn('status', ['published', 'reserved'])->count();
        $completedOffers = Offer::where('status', 'completed')->count();

        // Rezervacije
        $totalReservations  = Reservation::count();
        $activeReservations = Reservation::where('state', 'reserved')->count();

        // Poruke i ocene
        $totalMessages = Message::count();
        $totalRatings  = Rating::count();

        // Slanje podataka u view
        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'lockedUsers',
            'totalOffers',
            'activeOffers',
            'completedOffers',
            'totalReservations',
            'activeReservations',
            'totalMessages',
            'totalRatings'
        ));
    }
    // Lista svih korisnika
    public function users(Request $request)
    {
        $query = User::query();
        $q = $request->input('q');
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('naziv', 'like', "%" . $q . "%")
                    ->orWhere('email', 'like', "%" . $q . "%")
                    ->orWhere('maticni_broj', 'like', "%" . $q . "%");
            });
        }
        $users = $query->paginate(20)->appends(['q' => $q]);
        return view('admin.users.index', compact('users', 'q'));
    }

    // Postavljanje korisnika u istaknute ili uklanjanje
    public function featureUser($id, Request $request)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'is_featured' => 'required|boolean',
            'featured_rank' => 'nullable|integer|min:1',
        ]);
        $user->is_featured = $data['is_featured'];
        $user->featured_rank = $data['featured_rank'];
        $user->save();
        return back()->with('success', 'User feature updated');
    }

    // Zaključavanje korisničkog naloga
    public function lockUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'locked';
        $user->save();
        // set login attempts locked_until to 3 days from now
        $attempt = $user->loginAttempt()->firstOrCreate([]);
        $until = now()->addDays(3);
        $attempt->locked_until = $until;
        $attempt->attempts_count = 3;
        $attempt->last_attempt_at = now();
        $attempt->save();
        // Notify user
        $user->notify(new \App\Notifications\UserLockedNotification($until->toDateString()));
        // Notify all admins (excluding this admin) about lock
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            if ($admin->id !== auth()->id()) {
                $admin->notify(new \App\Notifications\UserLockedNotification($until->toDateString()));
            }
        }
        return back()->with('success', 'User locked');
    }

    // Otključavanje korisničkog naloga
    public function unlockUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->save();
        // Reset login attempts
        $user->loginAttempt()->update(['attempts_count' => 0, 'locked_until' => null]);
        // Notify user
        $user->notify(new \App\Notifications\UserUnlockedNotification());
        // Notify admins
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            if ($admin->id !== auth()->id()) {
                $admin->notify(new \App\Notifications\UserUnlockedNotification());
            }
        }
        return back()->with('success', 'User unlocked');
    }

    // Lista svih ponuda za admina
    public function offers(Request $request)
    {
        $query = Offer::with('user');
        $q = $request->input('q');
        if ($q) {
            $query->where(function ($sub) use ($q) {
                // search by ID, type, status, amount and user name/email
                $sub->where('id', $q)
                    ->orWhere('type', 'like', "%" . $q . "%")
                    ->orWhere('status', 'like', "%" . $q . "%")
                    ->orWhere('amount_eur', 'like', "%" . $q . "%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('naziv', 'like', "%" . $q . "%")
                            ->orWhere('email', 'like', "%" . $q . "%");
                    });
            });
        }
        $offers = $query->paginate(20)->appends(['q' => $q]);
        return view('admin.offers.index', compact('offers', 'q'));
    }

    public function reservations(Request $request)
    {
        $query = Reservation::with(['offer', 'seller', 'buyer']);
        $q = $request->input('q');
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('id', $q)
                    ->orWhere('state', 'like', "%" . $q . "%")
                    ->orWhereHas('seller', function ($seller) use ($q) {
                        $seller->where('naziv', 'like', "%" . $q . "%")
                            ->orWhere('email', 'like', "%" . $q . "%");
                    })
                    ->orWhereHas('buyer', function ($buyer) use ($q) {
                        $buyer->where('naziv', 'like', "%" . $q . "%")
                            ->orWhere('email', 'like', "%" . $q . "%");
                    })
                    ->orWhereHas('offer', function ($offer) use ($q) {
                        $offer->where('id', $q);
                    });
            });
        }
        $reservations = $query->paginate(20)->appends(['q' => $q]);
        return view('admin.reservations.index', compact('reservations', 'q'));
    }

    public function messages()
    {
        $messages = Message::with(['reservation', 'sender'])->paginate(50);
        return view('admin.messages.index', compact('messages'));
    }

    public function ratings()
    {
        $ratings = Rating::with(['reservation', 'rater', 'ratee'])->paginate(20);
        return view('admin.ratings.index', compact('ratings'));
    }

    // Prikaz forme za podešavanje limita platforme
    public function settings()
    {
        $maxPercent = Setting::get('max_percent', 50);
        $maxAmount = Setting::get('max_amount', 10000);
        return view('admin.settings.edit', compact('maxPercent', 'maxAmount'));
    }

    // Ažuriranje podešavanja platforme
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'max_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_amount' => ['required', 'numeric', 'min:0'],
        ]);
        Setting::set('max_percent', $data['max_percent']);
        Setting::set('max_amount', $data['max_amount']);
        return back()->with('success', 'Podešavanja su sačuvana.');
    }

    // Prikaz forme za kreiranje nove firme/korisnika
    public function createUser()
    {
        $cities = \App\Models\City::all();
        $industries = \App\Models\Industry::all();
        return view('admin.users.create', compact('cities', 'industries'));
    }

    // Čuvanje novog korisnika u bazi
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            // Matični broj must be exactly 8 digits and unique
            'maticni_broj' => ['required', 'digits:8', 'unique:users,maticni_broj'],
            'description' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'naziv' => ['required', 'string'],
            // Email must match a strict pattern and be unique
            'email' => ['required', 'email', 'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', 'unique:users,email'],
            'grad_id' => ['required', 'exists:cities,id'],
            'industry_id' => ['required', 'exists:industries,id'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Ensure the avatars directory exists inside public
            $avatarDir = public_path('avatars');
            if (!is_dir($avatarDir)) {
                @mkdir($avatarDir, 0755, true);
            }
            $avatar = $request->file('avatar');
            $filename = uniqid('avatar_') . '.' . $avatar->getClientOriginalExtension();
            // Move the uploaded file directly to the public avatars directory.  This avoids
            // reliance on a storage symlink and makes the image immediately accessible.
            $avatar->move($avatarDir, $filename);
            $data['avatar'] = 'avatars/' . $filename;
        } else {
            $data['avatar'] = '';
        }
        $password = Str::random(8);
        $user = User::create([
            'maticni_broj' => $data['maticni_broj'],
            'naziv' => $data['naziv'],
            'avatar' => $data['avatar'],
            'description' => $data['description'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'grad_id' => $data['grad_id'],
            'industry_id' => $data['industry_id'],
            'status' => 'active',
        ]);
        $user->assignRole('client');
        $user->notify(new \App\Notifications\NewUserNotification($password));
        return redirect()->route('admin.users')->with('success', 'Korisnik je uspešno kreiran i lozinka poslata.');
    }

    /**
     * Show edit form for a user (admin only).  Allows modification of
     * company name, email, city, and status.  Admin can optionally set
     * a new password which will be sent to the user via email.
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $cities = \App\Models\City::all();
        $industries = \App\Models\Industry::all();
        return view('admin.users.edit', compact('user', 'cities', 'industries'));
    }

    /**
     * Update user details from admin panel.
     */
    public function updateUser($id, Request $request)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'naziv' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
                'unique:users,email,' . $user->id
            ],
            'grad_id' => ['required', 'exists:cities,id'],
            'industry_id' => ['required', 'exists:industries,id'],
            'status' => ['required', 'in:active,inactive,locked'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        $user->naziv = $data['naziv'];
        $user->email = $data['email'];
        $user->grad_id = $data['grad_id'];
        $user->industry_id = $data['industry_id'];
        $user->status = $data['status'];
        $user->description = $data['description'];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Ensure the avatars directory exists inside public
            $avatarDir = public_path('avatars');
            if (!is_dir($avatarDir)) {
                @mkdir($avatarDir, 0755, true);
            }
            $avatar = $request->file('avatar');
            $filename = uniqid('avatar_') . '.' . $avatar->getClientOriginalExtension();
            // Move the uploaded file directly to the public avatars directory.  This avoids
            // reliance on a storage symlink and makes the image immediately accessible.
            $avatar->move($avatarDir, $filename);
            $data['avatar'] = 'avatars/' . $filename;
            $user->avatar = $data['avatar'];
        }
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        // If new password set, notify user via email
        if (!empty($data['password'])) {
            $user->notify(new \App\Notifications\NewUserNotification($data['password']));
        }
        return redirect()->route('admin.users')->with('success', 'Korisnik je ažuriran.');
    }

    /**
     * Reset user password and send a new one via email.
     */
    public function resetUserPassword($id)
    {
        $user = User::findOrFail($id);
        $password = Str::random(8);
        $user->password = Hash::make($password);
        $user->save();
        $user->notify(new \App\Notifications\NewUserNotification($password));
        return back()->with('success', 'Nova lozinka je poslana korisniku.');
    }

    /**
     * Display high level statistics for administrators.
     *
     * This dashboard aggregates counts across the entire platform to give
     * administrators a quick overview of the system state. Metrics include
     * the total number of registered companies, user status breakdowns,
     * counts of offers and reservations, and the volume of messages and ratings.
     */
    public function dashboard()
    {
        // Users
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();
        $lockedUsers = User::where('status', 'locked')->count();

        // Offers
        $totalOffers = Offer::count();
        $activeOffers = Offer::whereIn('status', ['published', 'reserved_partial', 'reserved_full', 'in_progress'])->count();
        $completedOffers = Offer::where('status', 'completed')->count();

        // Reservations
        $totalReservations = Reservation::count();
        $activeReservations = Reservation::where('state', '!=', 'canceled')->count();

        // Messages & Ratings
        $totalMessages = Message::count();
        $totalRatings = Rating::count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'lockedUsers',
            'totalOffers',
            'activeOffers',
            'completedOffers',
            'totalReservations',
            'activeReservations',
            'totalMessages',
            'totalRatings'
        ));
    }
}
