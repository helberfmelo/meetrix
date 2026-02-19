<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $apiKey;
    protected $instance;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.base_url');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->instance = config('services.whatsapp.instance');
    }

    /**
     * Send a Pro reminder to a guest.
     */
    public function sendReminder(string $phone, string $message)
    {
        if (!$this->baseUrl || !$this->apiKey) {
            Log::warning('WhatsApp Service not configured.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey
            ])->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                'number' => $phone,
                'text' => $message,
                'linkPreview' => true
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp reminder sent to {$phone}");
                return true;
            }

            Log::error("WhatsApp API Error: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp Service Exception: " . $e->getMessage());
            return false;
        }
    }
}
