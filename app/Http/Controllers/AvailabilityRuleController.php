<?php

namespace App\Http\Controllers;

use App\Models\AvailabilityRule;
use App\Models\SchedulingPage;
use Illuminate\Http\Request;

class AvailabilityRuleController extends Controller
{
    /**
     * Store or Update availability rules for a page.
     * Since usually a page has a set of rules, we might verify if we replace all or add one.
     * For the grid style, mostly it's a "Set of times" per page.
     * Let's assume we pass the page_id and a list of rules or a single rule.
     * For simplicity, let's treat it as CRUD on individual rules for now, or a "sync" endpoint.
     */
    public function index(Request $request, $pageId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();
        
        return response()->json($page->availabilityRules);
    }

    public function store(Request $request, $pageId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'days_of_week' => 'required|array',
            'start_time' => 'required|date_format:H:i:s', // or H:i
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'breaks' => 'nullable|array',
            'timezone' => 'nullable|string',
        ]);

        $rule = $page->availabilityRules()->create($validated);

        return response()->json($rule, 201);
    }

    public function update(Request $request, $pageId, $ruleId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();
        $rule = $page->availabilityRules()->findOrFail($ruleId);

        $validated = $request->validate([
            'days_of_week' => 'sometimes|array',
            'start_time' => 'sometimes|date_format:H:i:s',
            'end_time' => 'sometimes|date_format:H:i:s|after:start_time',
            'breaks' => 'nullable|array',
            'timezone' => 'nullable|string',
        ]);

        $rule->update($validated);

        return response()->json($rule);
    }

    public function destroy(Request $request, $pageId, $ruleId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();
        $page->availabilityRules()->findOrFail($ruleId)->delete();

        return response()->json(['message' => 'Rule deleted']);
    }

    /**
     * Replace all availability rules for the page with the provided list.
     */
    public function bulkUpdate(Request $request, $pageId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'rules' => 'present|array',
            'rules.*.days_of_week' => 'required|array',
            'rules.*.start_time' => 'required', // Allow H:i format
            'rules.*.end_time' => 'required',
            'rules.*.breaks' => 'nullable|array',
            'rules.*.timezone' => 'nullable|string',
        ]);

        // Transaction to ensure atomicity
        \DB::transaction(function () use ($page, $validated) {
            // Delete existing rules
            $page->availabilityRules()->delete();

            // Create new ones
            foreach ($validated['rules'] as $ruleData) {
                // Ensure times are properly formatted if needed, but DB usually handles "09:00" fine
                $page->availabilityRules()->create($ruleData);
            }
        });

        return response()->json($page->availabilityRules()->get());
    }
}
