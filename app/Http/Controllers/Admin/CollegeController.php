<?php

namespace App\Http\Controllers\Admin;

use App\Events\CollegeUpdated;
use App\Http\Controllers\Controller;
use App\Models\College;
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
        $colleges = College::orderBy('name')->get();

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
        ]);

        $college = College::create($validated);

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
        ]);

        $college->update($validated);

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
}
