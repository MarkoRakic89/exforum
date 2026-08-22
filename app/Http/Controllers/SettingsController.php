<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Show the form for editing the authenticated user's settings.
     */
    public function edit()
    {
        $user = Auth::user();
        $cities = City::all();
        return view('settings.edit', compact('user', 'cities'));
    }

    /**
     * Update the authenticated user's settings.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $rules = [
            'password' => 'nullable|string|min:8|confirmed',
        ];
        // Allow editing general info only for admins
        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');
        if ($isAdmin) {
            $rules['naziv'] = 'required|string|max:255';
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $user->id;
            $rules['grad_id'] = 'required|exists:cities,id';
        }
        $data = $request->validate($rules);
        if ($isAdmin) {
            $user->naziv = $data['naziv'];
            $emailChanged = $user->email !== $data['email'];
            $user->email = $data['email'];
            $user->grad_id = $data['grad_id'];
        }
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return back()->with('success', 'Podešavanja su ažurirana.');
    }
}