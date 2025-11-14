<?php

namespace App\Http\Controllers;

use App\Events\UserUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function index()
    {
        $user = Auth::user();
        $avatarColor = $this->getAvatarColor($user->first_name.$user->last_name);

        return view('profile.index', [
            'pageTitle' => 'Profile',
            'user' => $user,
            'avatarColor' => $avatarColor,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ]);

        // Broadcast the update to relevant channels (without notifications)
        broadcast(new UserUpdated($user, 'updated', 'self'));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
        ]);
    }

    /**
     * Change the user's password.
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Check if current password is correct
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Broadcast the password change to relevant channels (without notifications)
        broadcast(new UserUpdated($user, 'password_changed', 'self'));

        // Log activity
        \App\Services\ActivityLogService::log($user->id, 'password_change');

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!',
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function delete(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required',
        ]);

        // Check if password is correct
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect.',
            ], 422);
        }

        // Broadcast the deletion before deleting
        broadcast(new UserUpdated($user, 'deleted'));

        // Logout the user before deleting
        Auth::logout();

        // Delete the user (this will cascade delete related records due to foreign key constraints)
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully!',
        ]);
    }

    /**
     * Verify if the provided password is correct.
     */
    public function verifyPassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required',
        ]);

        // Check if password is correct
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    /**
     * Get avatar color based on first letter of name.
     */
    private function getAvatarColor($name)
    {
        $colors = [
            '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1',
            '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4',
        ];

        // Use only the first letter for consistent color
        $firstLetter = strtoupper(substr($name, 0, 1));
        $hash = ord($firstLetter);

        return $colors[$hash % count($colors)];
    }
}
