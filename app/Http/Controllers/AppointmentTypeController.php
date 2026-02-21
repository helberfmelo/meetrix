<?php

namespace App\Http\Controllers;

use App\Models\AppointmentType;
use App\Models\SchedulingPage;
use Illuminate\Http\Request;

class AppointmentTypeController extends Controller
{
    public function index(Request $request, $pageId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();
        
        return response()->json($page->appointmentTypes);
    }

    public function store(Request $request, $pageId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean'
        ]);

        $type = $page->appointmentTypes()->create($this->sanitizeTypeForAccountMode($validated, $user));

        return response()->json($type, 201);
    }

    public function update(Request $request, $pageId, $typeId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();
        $type = $page->appointmentTypes()->findOrFail($typeId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'duration_minutes' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'is_active' => 'boolean'
        ]);

        $type->update($this->sanitizeTypeForAccountMode($validated, $user));

        return response()->json($type);
    }

    public function destroy(Request $request, $pageId, $typeId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();
        $page->appointmentTypes()->findOrFail($typeId)->delete();

        return response()->json(['message' => 'Appointment Type deleted']);
    }

    /**
     * Replace all appointment types for the page.
     */
    public function bulkUpdate(Request $request, $pageId)
    {
        $user = $request->user();
        $page = SchedulingPage::where('id', $pageId)->where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'types' => 'present|array',
            'types.*.name' => 'required|string|max:255',
            'types.*.duration_minutes' => 'required|integer|min:1',
            'types.*.price' => 'required|numeric|min:0',
            'types.*.currency' => 'required|string|size:3',
            'types.*.is_active' => 'boolean'
        ]);

        \DB::transaction(function () use ($page, $validated, $user) {
            $page->appointmentTypes()->delete();
            foreach ($validated['types'] as $typeData) {
                $page->appointmentTypes()->create($this->sanitizeTypeForAccountMode($typeData, $user));
            }
        });

        return response()->json($page->appointmentTypes()->get());
    }

    private function sanitizeTypeForAccountMode(array $typeData, $user): array
    {
        if (($user->account_mode ?? 'scheduling_only') === 'scheduling_with_payments') {
            if (isset($typeData['currency'])) {
                $typeData['currency'] = strtoupper($typeData['currency']);
            }
            return $typeData;
        }

        $typeData['price'] = 0;
        $typeData['currency'] = strtoupper($user->currency ?? 'BRL');

        return $typeData;
    }
}
