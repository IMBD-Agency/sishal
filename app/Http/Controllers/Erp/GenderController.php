<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Gender;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    public function index()
    {
        $genders = Gender::latest()->get();
        return view('erp.genders.index', compact('genders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Gender::create($validated);
        return redirect()->back()->with('success', 'Gender created successfully.');
    }

    public function update(Request $request, Gender $gender)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $gender->update($validated);
        return redirect()->back()->with('success', 'Gender updated successfully.');
    }

    public function destroy(Gender $gender)
    {
        $gender->delete();
        return redirect()->back()->with('success', 'Gender deleted successfully.');
    }
}
