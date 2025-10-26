<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use App\Models\Administrator;
use App\Events\AdministratorUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdministratorController extends Controller
{
    /**
     * Show the administrator registration page.
     */
    public function index()
    {
        $adminRoles = AdminRole::orderBy('name')->get();
        
        return view('admin.registration.administrator', [
            'pageTitle' => 'Administrator Registration',
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:admin_roles,id'],
        ], [
            'email.unique' => 'Email is already registered',
            'email.regex' => 'Email must be from Gmail (@gmail.com) or DMMMSU (@dmmmsu.edu.ph)',
        ]);

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'administrator',
            'is_active' => true,
        ]);

        // Create administrator record
        $administrator = Administrator::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
        ]);

        // Broadcast the administrator creation event
        broadcast(new AdministratorUpdated($administrator, 'created'));

        return response()->json([
            'success' => true,
            'message' => 'Administrator registered successfully!',
            'user' => $user->load('administrator')
        ]);
    }

    /**
     * Check if email is available.
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $emailExists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => !$emailExists,
            'message' => $emailExists ? 'Email is already registered' : 'Email is available'
        ]);
    }
}
