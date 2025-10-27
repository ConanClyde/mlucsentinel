<?php

namespace App\Http\Controllers\Admin\Users;

use App\Events\NotificationCreated;
use App\Events\ReporterUpdated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Reporter;
use App\Models\ReporterType;
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
        $reporters = Reporter::with(['user', 'reporterType'])->get();
        $reporterTypes = ReporterType::all();

        return view('admin.users.reporters', [
            'reporters' => $reporters,
            'reporterTypes' => $reporterTypes,
        ]);
    }

    /**
     * Update reporter.
     */
    public function update(Request $request, Reporter $reporter)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$reporter->user_id,
            'type_id' => 'required|exists:reporter_types,id',
            'is_active' => 'required|boolean',
            'expiration_date' => 'nullable|date',
        ];

        // Only validate password if provided
        if ($request->has('password') && $request->password) {
            $rules['password'] = 'min:8|confirmed';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($reporter, $validated, $request) {
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
                'type_id' => $validated['type_id'],
                'expiration_date' => $validated['expiration_date'] ?? null,
            ]);

            // Broadcast the event with fresh relationships
            broadcast(new ReporterUpdated($reporter->fresh(['user', 'reporterType']), 'updated'));

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
