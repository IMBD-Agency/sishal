<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view products')) {
            abort(403, 'Unauthorized action.');
        }
        $seasons = Season::latest()->get();
        return view('erp.seasons.index', compact('seasons'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        Season::create($validated);
        return redirect()->back()->with('success', 'Season created successfully.');
    }

    public function update(Request $request, Season $season)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $season->update($validated);
        return redirect()->back()->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $season->delete();
        return redirect()->back()->with('success', 'Season deleted successfully.');
    }
}
