<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use Illuminate\Http\Request;

// Controller koji upravlja CRUD funkcijama za delatnosti u adminskoj sekciji.
class AdminIndustryController extends Controller
{
    // Prikaz liste svih delatnosti
    public function index(Request $request)
    {
        $query = Industry::query();
        $q = $request->input('q');
        // Allow searching by both name and code. When a search query
        // is provided we perform a simple LIKE filter across both
        // columns so administrators can locate industries either
        // by descriptive name or by their official code. Without a
        // query the full list is returned.
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%".$q."%")
                    ->orWhere('code', 'like', "%".$q."%");
            });
        }
        $industries = $query->orderBy('name')->paginate(20)->appends(['q' => $q]);
        return view('admin.industries.index', compact('industries','q'));
    }

    // Forma za unos nove delatnosti
    public function create()
    {
        return view('admin.industries.create');
    }

    // Čuvanje nove delatnosti u bazi
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);
        Industry::create($data);
        return redirect()->route('admin.industries.index')->with('success', 'Delatnost je uspešno dodata.');
    }

    // Brisanje delatnosti
    public function destroy($id)
    {
        $industry = Industry::findOrFail($id);
        $industry->delete();
        return back()->with('success', 'Delatnost je obrisana.');
    }

    // Prikaz forme za izmenu delatnosti
    public function edit($id)
    {
        $industry = Industry::findOrFail($id);
        return view('admin.industries.edit', compact('industry'));
    }

    // Ažuriranje delatnosti
    public function update($id, Request $request)
    {
        $industry = Industry::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);
        $industry->update($data);
        return redirect()->route('admin.industries.index')->with('success', 'Delatnost je ažurirana.');
    }
}