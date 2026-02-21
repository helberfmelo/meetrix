<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiError
{
    /**
     * Build a consistent API error payload with functional error codes.
     *
     * @param  array<string, mixed>  $meta
     */
    public static function response(
        string $message,
        string $errorCode,
        int $status = 422,
        array $meta = []
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'error_code' => $errorCode,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }
}
