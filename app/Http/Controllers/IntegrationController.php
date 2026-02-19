<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Crypt;

class IntegrationController extends Controller
{
    /**
     * Get Google OAuth Redirect URL.
     */
    public function googleRedirect(Request $request)
    {
        $client = $this->getGoogleClient();
        $authUrl = $client->createAuthUrl();

        return response()->json(['url' => $authUrl]);
    }

    /**
     * Handle Google OAuth Callback.
     */
    public function googleCallback(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $client = $this->getGoogleClient();
        
        try {
            $token = $client->fetchAccessTokenWithAuthCode($request->code);
            
            if (isset($token['error'])) {
                return response()->json(['error' => $token['error_description']], 400);
            }

            // Save or Update Integration
            $integration = Integration::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'service' => 'google'
                ],
                [
                    'token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($token['expires_in']),
                    'meta' => [
                        'email' => isset($token['id_token']) ? ($client->verifyIdToken($token['id_token'])['email'] ?? null) : null,
                    ]
                ]
            );

            return response()->json([
                'message' => 'Connected to Google!',
                'integration' => $integration
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List user integrations.
     */
    public function index(Request $request)
    {
        return response()->json($request->user()->integrations);
    }

    /**
     * Remove an integration.
     */
    public function destroy(Integration $integration)
    {
        $this->authorize('delete', $integration);
        $integration->delete();

        return response()->json(['message' => 'Integration removed.']);
    }

    private function getGoogleClient()
    {
        $client = new GoogleClient();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URL'));
        $client->addScope(\Google\Service\Calendar::CALENDAR);
        $client->addScope('email');
        $client->addScope('profile');
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client;
    }
}
