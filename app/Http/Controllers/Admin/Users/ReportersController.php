<?php

namespace App\Http\Controllers\Admin\Users;

use App\Events\NotificationCreated;
use App\Events\ReporterUpdated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Reporter;
use App\Models\ReporterRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReportersController extends Controller
{
    /**
     * Show the reporters page.
     */
    public function index()
    {
        $reporters = Reporter::with(['user', 'reporterRole'])->get();
        $reporterRoles = ReporterRole::orderBy('name')->get();

        return view('admin.users.reporters', [
            'reporters' => $reporters,
            'reporterRoles' => $reporterRoles,
        ]);
    }

    /**
     * Update reporter.
     */
    public function update(Request $request, Reporter $reporter)
    {
        // Authorization: Global Admin or admins with 'edit_reporters' privilege
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->hasPrivilege('edit_reporters')) {
            abort(403, 'You do not have permission to update reporters.');
        }

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$reporter->user_id,
            'reporter_role_id' => 'required|exists:reporter_roles,id',
            'is_active' => 'required|boolean',
        ];

        // Only validate password if provided
        if ($request->has('password') && $request->password) {
            $rules['password'] = 'min:8|confirmed';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($reporter, $validated, $request) {
            // Store old is_active status
            $oldIsActive = $reporter->user->is_active;

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
            $reporter->user->update($userData);

            // Update reporter
            $reporter->update([
                'reporter_role_id' => $validated['reporter_role_id'],
            ]);

            // Broadcast the event with fresh relationships
            broadcast(new ReporterUpdated($reporter->fresh(['user', 'reporterRole']), 'updated'));

            // Broadcast status change directly to the user if is_active changed
            if ($oldIsActive !== $validated['is_active']) {
                broadcast(new \App\Events\UserStatusChanged($reporter->user, $validated['is_active']));
            }

            // Create notification for all other administrators
            $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
            $reporterName = $reporter->user->first_name.' '.$reporter->user->last_name;

            User::whereIn('user_type', ['global_administrator', 'administrator'])
                ->where('id', '!=', auth()->id())
                ->get()
                ->each(function ($user) use ($editorName, $reporterName, $reporter) {
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'type' => 'reporter_updated',
                        'title' => 'Reporter Updated',
                        'message' => "{$editorName} updated Reporter {$reporterName}",
                        'data' => [
                            'reporter_id' => $reporter->id,
                            'action' => 'updated',
                            'url' => '/users/reporters',
                        ],
                    ]);

                    // Broadcast notification in real-time
                    broadcast(new NotificationCreated($notification));
                });
        });

        return response()->json([
            'success' => true,
            'message' => 'Reporter updated successfully!',
            'editor' => auth()->user()->first_name.' '.auth()->user()->last_name,
        ]);
    }

    /**
     * Delete reporter.
     */
    public function destroy(Reporter $reporter)
    {
        // Authorization: Global Admin or admins with 'delete_reporters' privilege
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->hasPrivilege('delete_reporters')) {
            abort(403, 'You do not have permission to delete reporters.');
        }

        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $reporterName = $reporter->user->first_name.' '.$reporter->user->last_name;

        DB::transaction(function () use ($reporter, $editorName, $reporterName) {
            // Broadcast the event before deletion
            broadcast(new ReporterUpdated($reporter, 'deleted'));

            // Create notification for all other administrators
            User::whereIn('user_type', ['global_administrator', 'administrator'])
                ->where('id', '!=', auth()->id())
                ->get()
                ->each(function ($user) use ($editorName, $reporterName) {
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'type' => 'reporter_deleted',
                        'title' => 'Reporter Removed',
                        'message' => "{$editorName} removed {$reporterName}",
                        'data' => [
                            'action' => 'deleted',
                            'url' => '/users/reporters',
                        ],
                    ]);

                    // Broadcast notification in real-time
                    broadcast(new NotificationCreated($notification));
                });

            // Delete reporter and user
            $reporter->user->delete(); // This will cascade delete the reporter
        });

        return response()->json([
            'success' => true,
            'message' => 'Reporter deleted successfully!',
        ]);
    }
}
