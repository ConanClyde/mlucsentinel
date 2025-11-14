<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\ReporterUpdated;
use App\Http\Controllers\Controller;
use App\Models\Reporter;
use App\Models\ReporterRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ReporterController extends Controller
{
    /**
     * Show the reporter registration page.
     */
    public function index()
    {
        $reporterRoles = ReporterRole::active()->orderBy('name')->get();

        return view('admin.registration.reporter', [
            'pageTitle' => 'Reporter Registration',
            'reporterRoles' => $reporterRoles,
        ]);
    }

    /**
     * Store a newly created reporter.
     */
    public function store(Request $request)
    {
        // Authorization: Global Admin or admins with 'register_reporters' privilege
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->hasPrivilege('register_reporters')) {
            abort(403, 'You do not have permission to register reporters.');
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph|student\.dmmmsu\.edu\.ph)$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'reporter_role_id' => ['required', 'exists:reporter_roles,id'],
        ], [
            'email.unique' => 'Email is already registered',
            'email.regex' => 'Email must be from Gmail (@gmail.com), DMMMSU (@dmmmsu.edu.ph), or Student DMMMSU (@student.dmmmsu.edu.ph)',
        ]);

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name.' '.$request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'reporter',
            'is_active' => true,
        ]);

        // Create reporter record
        $reporter = Reporter::create([
            'user_id' => $user->id,
            'reporter_role_id' => $request->reporter_role_id,
            'is_active' => true,
        ]);

        // Broadcast the reporter creation event
        broadcast(new ReporterUpdated($reporter, 'created'));

        return response()->json([
            'success' => true,
            'message' => 'Reporter registered successfully!',
            'user' => $user->load('reporter'),
        ]);
    }

    /**
     * Check if email is available.
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $emailExists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => ! $emailExists,
            'message' => $emailExists ? 'Email is already registered' : 'Email is available',
        ]);
    }
}
