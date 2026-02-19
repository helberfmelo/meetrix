<?php

namespace App\Http\Controllers;

use App\Models\SchedulingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $teamIds = $user->teams->pluck('id');

        // Fetch pages owned by user OR belonging to user's teams
        $pages = SchedulingPage::where('user_id', $user->id)
            ->orWhereIn('team_id', $teamIds)
            ->get();

        return response()->json($pages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:scheduling_pages,slug',
            'intro_text' => 'nullable|string',
        ]);

        $user = $request->user();

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'slug' => $request->slug,
            'intro_text' => $request->intro_text,
            'is_active' => true,
            'config' => [], // Default empty config
        ]);

        return response()->json($page, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $page = SchedulingPage::where('slug', $slug)
            ->where('is_active', true)
            ->with(['appointmentTypes', 'availabilityRules'])
            ->firstOrFail();

        $page->increment('views');
        
        return response()->json($page);
    }

    /**
     * Record a click on a time slot.
     */
    public function recordClick(string $slug)
    {
        $page = SchedulingPage::where('slug', $slug)->firstOrFail();
        $page->increment('slot_clicks');
        return response()->json(['success' => true]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $teamIds = $user->teams->pluck('id');
        
        $page = SchedulingPage::where('id', $id)
            ->where(function($q) use ($user, $teamIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('team_id', $teamIds);
            })->firstOrFail();

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'intro_text' => 'nullable|string',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $page->update($request->only(['title', 'intro_text', 'config', 'is_active', 'team_id', 'confirmation_message', 'redirect_url']));

        return response()->json($page);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = request()->user();
        $teamIds = $user->teams->pluck('id');

        $page = SchedulingPage::where('id', $id)
            ->where(function($q) use ($user, $teamIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('team_id', $teamIds);
            })->firstOrFail();
        
        $page->delete();

        return response()->json(['message' => 'Page deleted successfully']);
    }
}
