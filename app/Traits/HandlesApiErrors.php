<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait HandlesApiErrors
{
    /**
     * Handle API errors with standardized responses
     */
    protected function handleApiError(\Throwable $e, string $context = 'operation'): JsonResponse
    {
        // Log the full exception details
        \Log::error("{$context} failed", [
            'exception' => $e,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        // Return user-friendly error message
        return response()->json([
            'success' => false,
            'message' => ucfirst($context).' failed. Please try again.',
        ], $this->getStatusCode($e));
    }

    /**
     * Get appropriate status code for exception
     */
    protected function getStatusCode(\Throwable $e): int
    {
        if ($e instanceof ValidationException) {
            return 422;
        }

        return 500;
    }
}
