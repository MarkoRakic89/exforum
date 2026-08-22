<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

// Controller koji upravlja CRUD funkcijama za gradove u adminskoj sekciji.
class AdminCityController extends Controller
{
    // Prikaz liste svih gradova
    public function index(Request $request)
    {
        $query = City::query();
        $q = $request->input('q');
        if ($q) {
            $query->where('name','like', "%".$q."%");
        }
        $cities = $query->orderBy('name')->paginate(20)->appends(['q' => $q]);
        return view('admin.cities.index', compact('cities','q'));
    }

    // Forma za unos novog grada
    public function create()
    {
        return view('admin.cities.create');
    }

    // Čuvanje novog grada u bazi
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        City::create($data);
        return redirect()->route('admin.cities.index')->with('success', 'Grad je uspešno dodat.');
    }

    // Brisanje grada
    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();
        return back()->with('success', 'Grad je obrisan.');
    }

    // Prikaz forme za izmenu grada
    public function edit($id)
    {
        $city = City::findOrFail($id);
        return view('admin.cities.edit', compact('city'));
    }

    // Ažuriranje podataka grada
    public function update($id, Request $request)
    {
        $city = City::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $city->update($data);
        return redirect()->route('admin.cities.index')->with('success', 'Grad je ažuriran.');
    }
}