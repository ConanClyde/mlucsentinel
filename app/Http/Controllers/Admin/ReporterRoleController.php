<?php

namespace App\Http\Controllers\Admin;

use App\Events\ReporterRoleUpdated;
use App\Http\Controllers\Controller;
use App\Models\ReporterRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReporterRoleController extends Controller
{
    /**
     * Get all reporter roles with their permissions.
     */
    public function index()
    {
        $roles = ReporterRole::orderBy('name')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'is_active' => $role->is_active,
                'allowed_user_types' => $role->getAllowedUserTypes(),
                'reporters_count' => $role->reporters()->count(),
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ];
        });

        return response()->json($roles);
    }

    /**
     * Get available user types that can be reported.
     */
    public function getAvailableUserTypes()
    {
        $userTypes = [
            ['value' => 'student', 'label' => 'Students'],
            ['value' => 'staff', 'label' => 'Staff'],
            ['value' => 'security', 'label' => 'Security Personnel'],
            ['value' => 'stakeholder', 'label' => 'Stakeholders'],
            ['value' => 'reporter', 'label' => 'Reporters'],
        ];

        return response()->json($userTypes);
    }

    /**
     * Store a new reporter role.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:reporter_roles,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'allowed_user_types' => 'required|array|min:1',
            'allowed_user_types.*' => 'string|in:student,staff,security,stakeholder,reporter',
            'default_expiration_years' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = ReporterRole::create([
            'name' => $request->name,
            'description' => $request->description,
            'default_expiration_years' => $request->default_expiration_years,
            'is_active' => $request->is_active ?? true,
        ]);

        // Sync the allowed user types
        $role->syncUserTypes($request->allowed_user_types);

        // Broadcast the event
        broadcast(new ReporterRoleUpdated($role, 'created'));

        return response()->json([
            'success' => true,
            'message' => 'Reporter role created successfully',
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'is_active' => $role->is_active,
                'allowed_user_types' => $role->getAllowedUserTypes(),
            ],
        ]);
    }

    /**
     * Update an existing reporter role.
     */
    public function update(Request $request, ReporterRole $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:reporter_roles,name,'.$role->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'allowed_user_types' => 'required|array|min:1',
            'allowed_user_types.*' => 'string|in:student,staff,security,stakeholder,reporter',
            'default_expiration_years' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'default_expiration_years' => $request->default_expiration_years,
            'is_active' => $request->is_active ?? $role->is_active,
        ]);

        // Sync the allowed user types
        $role->syncUserTypes($request->allowed_user_types);

        // Broadcast the event
        broadcast(new ReporterRoleUpdated($role, 'updated'));

        return response()->json([
            'success' => true,
            'message' => 'Reporter role updated successfully',
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'is_active' => $role->is_active,
                'allowed_user_types' => $role->getAllowedUserTypes(),
            ],
        ]);
    }

    /**
     * Delete a reporter role.
     */
    public function destroy(ReporterRole $role)
    {
        // Check if role has reporters
        $reportersCount = $role->reporters()->count();

        if ($reportersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete role. {$reportersCount} reporter(s) are currently assigned to this role.",
            ], 422);
        }

        // Broadcast the event before deletion
        broadcast(new ReporterRoleUpdated($role, 'deleted'));

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reporter role deleted successfully',
        ]);
    }

    /**
     * Toggle role active status.
     */
    public function toggleActive(ReporterRole $role)
    {
        $role->update(['is_active' => ! $role->is_active]);

        // Broadcast the event
        broadcast(new ReporterRoleUpdated($role, 'updated'));

        return response()->json([
            'success' => true,
            'message' => 'Reporter role status updated successfully',
            'is_active' => $role->is_active,
        ]);
    }
}
