<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;

class PollController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->polls()->withCount('options')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'options' => 'required|array|min:1',
            'options.*.start_at' => 'required|date',
            'options.*.end_at' => 'required|date|after:options.*.start_at',
        ]);

        $poll = Poll::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . rand(1000, 9999),
        ]);

        foreach ($request->options as $option) {
            $poll->options()->create($option);
        }

        return response()->json($poll->load('options'), 201);
    }

    public function show($slug)
    {
        $poll = Poll::where('slug', $slug)
            ->where('is_active', true)
            ->with(['options' => function($q) {
                $q->withCount('votes');
            }])
            ->firstOrFail();

        return response()->json($poll);
    }

    public function vote(Request $request, PollOption $option)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
        ]);

        // One vote per option per email
        $vote = PollVote::updateOrCreate(
            ['poll_option_id' => $option->id, 'customer_email' => $request->customer_email],
            ['customer_name' => $request->customer_name]
        );

        return response()->json(['message' => 'Vote cast successfully!', 'vote' => $vote]);
    }
}
