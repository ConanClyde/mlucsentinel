<?php

namespace App\Http\Controllers\Admin;

use App\Events\CollegeUpdated;
use App\Http\Controllers\Controller;
use App\Models\College;
use App\Services\StaticDataCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollegeController extends Controller
{
    /**
     * Display a listing of colleges.
     */
    public function index(): JsonResponse
    {
        $colleges = StaticDataCacheService::getColleges();

        return response()->json([
            'success' => true,
            'data' => $colleges,
        ]);
    }

    /**
     * Store a newly created college.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:colleges,name'],
            'code' => ['required', 'string', 'max:20', 'unique:colleges,code'],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['type'] = $validated['type'] ?? 'college';
        $validated['description'] = $validated['description'] ?? null;

        $college = College::create($validated)->refresh();

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new CollegeUpdated($college, 'created', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'College created successfully',
            'data' => $college,
        ], 201);
    }

    /**
     * Update the specified college.
     */
    public function update(Request $request, College $college): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('colleges')->ignore($college->id)],
            'code' => ['required', 'string', 'max:20', Rule::unique('colleges', 'code')->ignore($college->id)],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['type'] = $validated['type'] ?? 'college';
        $validated['description'] = $validated['description'] ?? null;

        $college->update($validated);
        $college->refresh();

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new CollegeUpdated($college, 'updated', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'College updated successfully',
            'data' => $college,
        ]);
    }

    /**
     * Remove the specified college.
     */
    public function destroy(College $college): JsonResponse
    {
        // Check if college has students
        if ($college->students()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete college with existing students',
            ], 422);
        }

        // Get editor name before deleting
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;
        $collegeData = $college->toArray();

        $college->delete();

        // Broadcast the event with deleted college data
        broadcast(new CollegeUpdated($college, 'deleted', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'College deleted successfully',
        ]);
    }

    /**
     * Get programs for a specific college.
     */
    public function programs(College $college): JsonResponse
    {
        $programs = $college->programs()->orderBy('name')->get();

        return response()->json($programs);
    }
}
