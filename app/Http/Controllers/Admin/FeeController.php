<?php

namespace App\Http\Controllers\Admin;

use App\Events\FeeUpdated;
use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Services\AuditLogService;
use App\Services\CacheInvalidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * Get all fees
     */
    public function index(): JsonResponse
    {
        $fees = Fee::orderBy('display_name')->get();

        return response()->json($fees);
    }

    /**
     * Update a fee
     */
    public function update(Request $request, Fee $fee): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $oldAmount = $fee->amount;

        $fee->update([
            'amount' => $validated['amount'],
        ]);

        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Log the fee change
        AuditLogService::log('fee_updated', $fee, ['amount' => $oldAmount], ['amount' => $fee->amount]);

        // Clear analytics cache as revenue calculations depend on fee amounts
        CacheInvalidationService::clearAllAnalyticsCache();

        broadcast(new FeeUpdated($fee->fresh(), 'updated', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Fee updated successfully',
            'fee' => $fee->fresh(),
        ]);
    }
}
