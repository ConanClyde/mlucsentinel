<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminRoleUpdated;
use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\Privilege;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRoleController extends Controller
{
    /**
     * Display a listing of admin roles.
     */
    public function index(): View
    {
        return view('admin.settings.roles.index', [
            'pageTitle' => 'Admin Roles',
        ]);
    }

    /**
     * Get all admin roles with their privileges (API).
     */
    public function getRoles(): JsonResponse
    {
        $roles = AdminRole::with('privileges')
            ->withCount('administrators')
            ->orderBy('name')
            ->get();

        // Attach report_targets to each role
        $roles->each(function ($role) {
            $role->setAttribute('report_targets', $role->getReportTargets());
        });

        return response()->json([
            'success' => true,
            'roles' => $roles,
        ]);
    }

    /**
     * Get all available privileges (API).
     */
    public function getPrivileges(): JsonResponse
    {
        $privileges = Privilege::orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return response()->json([
            'success' => true,
            'privileges' => $privileges,
        ]);
    }

    /**
     * Store a newly created admin role.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:admin_roles,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'can_register_users' => ['boolean'],
            'can_edit_users' => ['boolean'],
            'can_delete_users' => ['boolean'],
            'privileges' => ['array'],
            'privileges.*' => ['exists:privileges,id'],
            'report_targets' => ['array'],
            'report_targets.*' => ['string', 'in:student,staff,security,stakeholder,reporter'],
        ]);

        $role = AdminRole::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'can_register_users' => $validated['can_register_users'] ?? false,
            'can_edit_users' => $validated['can_edit_users'] ?? false,
            'can_delete_users' => $validated['can_delete_users'] ?? false,
        ]);

        // Attach privileges if provided
        if (isset($validated['privileges'])) {
            $role->privileges()->sync($validated['privileges']);
        }

        // Sync report targets if provided
        $role->syncReportTargets($validated['report_targets'] ?? []);

        // Broadcast realtime update
        event(new AdminRoleUpdated($role->load('privileges'), 'created', optional(auth()->user())->email));

        return response()->json([
            'success' => true,
            'message' => 'Admin role created successfully',
            'role' => $role->load('privileges')->setAttribute('report_targets', $role->getReportTargets()),
        ], 201);
    }

    /**
     * Update the specified admin role.
     */
    public function update(Request $request, AdminRole $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:admin_roles,name,'.$role->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'can_register_users' => ['boolean'],
            'can_edit_users' => ['boolean'],
            'can_delete_users' => ['boolean'],
            'privileges' => ['array'],
            'privileges.*' => ['exists:privileges,id'],
            'report_targets' => ['array'],
            'report_targets.*' => ['string', 'in:student,staff,security,stakeholder,reporter'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'can_register_users' => $validated['can_register_users'] ?? false,
            'can_edit_users' => $validated['can_edit_users'] ?? false,
            'can_delete_users' => $validated['can_delete_users'] ?? false,
        ]);

        // Sync privileges
        if (isset($validated['privileges'])) {
            $role->privileges()->sync($validated['privileges']);
        }

        // Sync report targets if provided
        $role->syncReportTargets($validated['report_targets'] ?? []);

        // Broadcast realtime update
        event(new AdminRoleUpdated($role->fresh()->load('privileges'), 'updated', optional(auth()->user())->email));

        return response()->json([
            'success' => true,
            'message' => 'Admin role updated successfully',
            'role' => $role->fresh()->load('privileges')->setAttribute('report_targets', $role->getReportTargets()),
        ]);
    }

    /**
     * Remove the specified admin role.
     */
    public function destroy(AdminRole $role): JsonResponse
    {
        // Prevent deletion if role has administrators
        if ($role->administrators()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role with assigned administrators. Please reassign them first.',
            ], 422);
        }

        $role->delete();

        // Broadcast realtime update
        event(new AdminRoleUpdated($role, 'deleted', optional(auth()->user())->email));

        return response()->json([
            'success' => true,
            'message' => 'Admin role deleted successfully',
        ]);
    }
}
