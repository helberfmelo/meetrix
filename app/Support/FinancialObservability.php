<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class FinancialObservability
{
    /**
     * Standardized warning payload for recoverable business-rule errors.
     *
     * @param  array<string, mixed>  $context
     */
    public static function warning(string $flow, string $errorCode, string $message, array $context = []): void
    {
        Log::warning('financial_flow_warning', [
            'flow' => $flow,
            'error_code' => $errorCode,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Standardized error payload for hard failures on financial flows.
     *
     * @param  array<string, mixed>  $context
     */
    public static function error(string $flow, string $errorCode, string $message, array $context = []): void
    {
        Log::error('financial_flow_error', [
            'flow' => $flow,
            'error_code' => $errorCode,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
