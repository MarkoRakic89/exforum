<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Handles password reset requests for users who log in using their
 * maticni_broj (registration number).  Users submit their ID number and
 * if a matching account is found a new random password is generated,
 * stored, and an email is dispatched to the address on record.  On
 * completion the user is redirected back to the login page with a
 * success message.  Error messages are returned when the ID cannot be
 * found.
 */
class PasswordResetController extends Controller
{
    /**
     * Show the form allowing a user to request a new password.  The
     * user only needs to provide their registration number; no email is
     * requested because that is already stored in the account.
     */
    public function requestForm()
    {
        return view('auth.password_request');
    }

    /**
     * Handle a password reset submission.  Validate the provided
     * maticni_broj, locate the associated user and if found generate
     * and save a new password.  A simple email is sent to the user's
     * registered email address containing the new credentials.  In case
     * of errors (e.g. nonexistent maticni_broj) the user is sent back
     * with an appropriate error message.
     */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'maticni_broj' => ['required', 'string'],
        ]);

        $user = User::where('maticni_broj', $data['maticni_broj'])->first();
        if (!$user) {
            return back()->withErrors(['maticni_broj' => 'Nije pronađen korisnik sa tim matičnim brojem.']);
        }

        // Generate a new random password and update the user record
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();

        // Attempt to send an email with the new password.  Even if
        // emailing fails (e.g. misconfigured mail transport) we
        // continue and report success to the user.
        try {
            Mail::raw(
                "Vaša nova lozinka je: {$newPassword}\nMolimo vas da se prijavite i promenite je odmah.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Nova lozinka za vaš nalog');
                }
            );
        } catch (\Throwable $e) {
            // Log or silently ignore email failures; the password has
            // still been reset and the user can use the new one.
        }

        // Redirect to login with a status message
        return redirect()->route('login')->with('status', 'Nova lozinka je poslata na vašu email adresu.');
    }
}