<?php

namespace App\Http\Controllers\Api\Concerns;

trait ApiResponse
{
    /**
     * Build a standardized success JSON response.
     */
    protected function sendResponse($result, $message)
    {
        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => $message,
        ], 200);
    }

    /**
     * Build a standardized error JSON response.
     */
    protected function sendError($error, $errorMessages = [], $code = 404)
    {
        $payload = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $payload['data'] = $errorMessages;
        }

        return response()->json($payload, $code);
    }
}
