<?php

namespace App\Http\Controllers\Admin;

use App\Events\StakeholderTypeUpdated;
use App\Http\Controllers\Controller;
use App\Models\StakeholderType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StakeholderTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = StakeholderType::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:stakeholder_types,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'evidence_required' => ['boolean'],
        ]);

        $type = StakeholderType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'evidence_required' => $validated['evidence_required'] ?? true,
        ]);

        // clear cache
        \App\Services\StaticDataCacheService::clearCacheByModel('StakeholderType');

        // Broadcast event
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        broadcast(new StakeholderTypeUpdated($type, 'created', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $type,
        ], 201);
    }

    public function update(Request $request, StakeholderType $stakeholderType): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:stakeholder_types,name,'.$stakeholderType->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'evidence_required' => ['boolean'],
        ]);

        $stakeholderType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'evidence_required' => $validated['evidence_required'] ?? true,
        ]);

        // clear cache
        \App\Services\StaticDataCacheService::clearCacheByModel('StakeholderType');

        // Broadcast event
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        broadcast(new StakeholderTypeUpdated($stakeholderType->fresh(), 'updated', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $stakeholderType,
        ]);
    }

    public function destroy(StakeholderType $stakeholderType): JsonResponse
    {
        // prevent delete if in use
        if ($stakeholderType->stakeholders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: there are stakeholders using this type.',
            ], 422);
        }

        // Broadcast event before deletion
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        broadcast(new StakeholderTypeUpdated($stakeholderType, 'deleted', $editor))->toOthers();

        $stakeholderType->delete();

        // clear cache
        \App\Services\StaticDataCacheService::clearCacheByModel('StakeholderType');

        return response()->json([
            'success' => true,
            'message' => 'Deleted',
        ]);
    }
}
