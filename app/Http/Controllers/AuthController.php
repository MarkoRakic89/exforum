<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt using maticni_broj and password.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'maticni_broj' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('maticni_broj', $credentials['maticni_broj'])->first();
        if (!$user) {
            return back()->withErrors(['maticni_broj' => 'Invalid credentials.']);
        }

        // Check if account is locked
        $loginAttempt = LoginAttempt::firstOrCreate(['user_id' => $user->id]);
        $now = now();
        if ($loginAttempt->locked_until && $loginAttempt->locked_until->isFuture()) {
            $remaining = $loginAttempt->locked_until->diffForHumans($now, true);
            return back()->withErrors(['maticni_broj' => "Account locked. Try again in {$remaining}."]);
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            // Failed attempt
            $loginAttempt->attempts_count += 1;
            $loginAttempt->last_attempt_at = $now;
            // Lock if 3 failed attempts within 24 hours
            if ($loginAttempt->attempts_count >= 3 && $loginAttempt->last_attempt_at && $loginAttempt->last_attempt_at->gt($now->subHours(24))) {
                $loginAttempt->locked_until = $now->copy()->addDays(3);
                $user->status = 'locked';
                $user->save();
            }
            $loginAttempt->save();
            return back()->withErrors(['password' => 'Invalid credentials.']);
        }

        // Successful login
        $loginAttempt->attempts_count = 0;
        $loginAttempt->locked_until = null;
        $loginAttempt->save();

        if ($user->status === 'locked') {
            return back()->withErrors(['maticni_broj' => 'Account is currently locked.']);
        }

        Auth::login($user, false);
        // Redirect admins to the admin dashboard, others to the regular dashboard
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}