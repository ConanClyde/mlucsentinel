<?php

namespace App\Http\Controllers\Admin;

use App\Events\ProgramUpdated;
use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    /**
     * Display a listing of programs.
     */
    public function index(): JsonResponse
    {
        $programs = Program::with('college')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    /**
     * Store a newly created program.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:programs,code'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['description'] = $validated['description'] ?? null;

        $program = Program::create($validated)->load('college');
        $program->load('college');

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new ProgramUpdated($program, 'created', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Program created successfully',
            'data' => $program,
        ], 201);
    }

    /**
     * Update the specified program.
     */
    public function update(Request $request, Program $program): JsonResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('programs', 'code')->ignore($program->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['description'] = $validated['description'] ?? null;

        $program->update($validated);
        $program->load('college');

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new ProgramUpdated($program, 'updated', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Program updated successfully',
            'data' => $program,
        ]);
    }

    /**
     * Remove the specified program.
     */
    public function destroy(Program $program): JsonResponse
    {
        // Check if program has students
        if ($program->students()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete program with existing students',
            ], 422);
        }

        // Get editor name before deleting
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Load relationship before deleting
        $program->load('college');

        // Store data for broadcasting after deletion
        $programData = $program->toArray();
        if ($program->college) {
            $programData['college'] = $program->college->toArray();
        }

        $program->delete();

        // Create a temporary Program instance for broadcasting
        $programForBroadcast = new Program($programData);
        $programForBroadcast->id = $programData['id'];
        $programForBroadcast->exists = false;

        // Set the college relationship manually if it exists
        if (isset($programData['college'])) {
            $programForBroadcast->setRelation('college', \App\Models\College::make($programData['college']));
        }

        // Broadcast the event with deleted program data
        broadcast(new ProgramUpdated($programForBroadcast, 'deleted', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully',
        ]);
    }
}
