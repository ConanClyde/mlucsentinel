<?php

namespace App\Http\Controllers\Admin\Users;

use App\Events\AdministratorUpdated;
use App\Events\NotificationCreated;
use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\AdminRole;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministratorsController extends Controller
{
    /**
     * Show the administrators page.
     */
    public function index()
    {
        $administrators = Administrator::with(['user', 'adminRole'])
            ->orderBy('created_at', 'desc')
            ->get();

        $adminRoles = AdminRole::orderBy('name')->get();

        return view('admin.users.administrators', [
            'pageTitle' => 'Administrators Management',
            'administrators' => $administrators,
            'adminRoles' => $adminRoles,
        ]);
    }

    /**
     * Store a newly created administrator.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:admin_roles,id'],
        ]);

        DB::transaction(function () use ($request) {
            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'administrator',
            ]);

            // Create administrator record
            $administrator = Administrator::create([
                'user_id' => $user->id,
                'role_id' => $request->role_id,
            ]);

            // Broadcast the event
            broadcast(new AdministratorUpdated($administrator, 'created'));
        });

        return response()->json([
            'success' => true,
            'message' => 'Administrator created successfully!',
        ]);
    }

    /**
     * Update the specified administrator.
     */
    public function update(Request $request, Administrator $administrator)
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$administrator->user_id],
            'is_active' => ['required', 'boolean'],
        ];

        // Only validate password if provided
        if ($request->has('password') && $request->password) {
            $rules['password'] = ['min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $administrator, $request) {
            // Prepare user update data
            $userData = [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ];

            // Update password if provided
            if ($request->has('password') && $request->password) {
                $userData['password'] = Hash::make($request->password);
            }

            // Update user
            $administrator->user->update($userData);

            // Administrator role cannot be changed through edit

            // Broadcast the event with fresh relationships
            broadcast(new AdministratorUpdated($administrator->fresh(['user', 'adminRole']), 'updated'));

            // Create notification for all other administrators
            $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
            $adminName = $administrator->user->first_name.' '.$administrator->user->last_name;

            User::whereIn('user_type', ['global_administrator', 'administrator'])
                ->where('id', '!=', auth()->id())
                ->get()
                ->each(function ($user) use ($editorName, $adminName, $administrator) {
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'type' => 'administrator_updated',
                        'title' => 'Administrator Updated',
                        'message' => "{$editorName} updated Administrator {$adminName}",
                        'data' => [
                            'administrator_id' => $administrator->id,
                            'action' => 'updated',
                            'url' => '/users/administrators',
                        ],
                    ]);

                    // Broadcast notification in real-time
                    broadcast(new NotificationCreated($notification));
                });
        });

        return response()->json([
            'success' => true,
            'message' => 'Administrator updated successfully!',
            'editor' => auth()->user()->first_name.' '.auth()->user()->last_name,
        ]);
    }

    /**
     * Remove the specified administrator.
     */
    public function destroy(Administrator $administrator)
    {
        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $adminName = $administrator->user->first_name.' '.$administrator->user->last_name;

        DB::transaction(function () use ($administrator, $editorName, $adminName) {
            // Broadcast the event before deletion
            broadcast(new AdministratorUpdated($administrator, 'deleted'));

            // Create notification for all other administrators
            User::whereIn('user_type', ['global_administrator', 'administrator'])
                ->where('id', '!=', auth()->id())
                ->get()
                ->each(function ($user) use ($editorName, $adminName) {
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'type' => 'administrator_deleted',
                        'title' => 'Administrator Removed',
                        'message' => "{$editorName} removed {$adminName}",
                        'data' => [
                            'action' => 'deleted',
                            'url' => '/users/administrators',
                        ],
                    ]);

                    // Broadcast notification in real-time
                    broadcast(new NotificationCreated($notification));
                });

            // Delete administrator and user
            $administrator->user->delete(); // This will cascade delete the administrator
        });

        return response()->json([
            'success' => true,
            'message' => 'Administrator deleted successfully!',
        ]);
    }
}
