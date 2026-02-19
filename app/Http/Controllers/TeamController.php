<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * List teams the user belongs to.
     */
    public function index(Request $request)
    {
        return response()->json($request->user()->teams()->with('owner')->get());
    }

    /**
     * Create a new team.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(100, 999),
        ]);

        // Attach owner as admin
        $team->users()->attach($request->user()->id, ['role' => 'owner']);

        return response()->json($team->load('users'), 201);
    }

    /**
     * Invite a user to the team.
     */
    public function invite(Request $request, Team $team)
    {
        // Simple logic for now: add if user exists, otherwise fail.
        // In a real scenario, this would send an invite email.
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($team->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already a member.'], 422);
        }

        $team->users()->attach($user->id, ['role' => 'member']);

        return response()->json(['message' => 'Member added successfully.']);
    }
}
