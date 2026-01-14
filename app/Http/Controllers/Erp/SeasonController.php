<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::latest()->get();
        return view('erp.seasons.index', compact('seasons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        Season::create($validated);
        return redirect()->back()->with('success', 'Season created successfully.');
    }

    public function update(Request $request, Season $season)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $season->update($validated);
        return redirect()->back()->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season)
    {
        $season->delete();
        return redirect()->back()->with('success', 'Season deleted successfully.');
    }
}
