<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    /**
     * Mark onboarding as completed for the authenticated user.
     */
    public function complete(Request $request)
    {
        $user = $request->user();
        
        $user->update([
            'onboarding_completed_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Onboarding completed successfully',
            'user' => $user
        ]);
    }
}
