<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\ReporterUpdated;
use App\Http\Controllers\Controller;
use App\Models\Reporter;
use App\Models\ReporterType;
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
        $reporterTypes = ReporterType::orderBy('name')->get();

        return view('admin.registration.reporter', [
            'pageTitle' => 'Reporter Registration',
            'reporterTypes' => $reporterTypes,
        ]);
    }

    /**
     * Store a newly created reporter.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph|student\.dmmmsu\.edu\.ph)$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type_id' => ['required', 'exists:reporter_types,id'],
        ], [
            'email.unique' => 'Email is already registered',
            'email.regex' => 'Email must be from Gmail (@gmail.com), DMMMSU (@dmmmsu.edu.ph), or Student DMMMSU (@student.dmmmsu.edu.ph)',
        ]);

        // Get reporter type to determine expiration date
        $reporterType = ReporterType::find($request->type_id);
        $expirationDate = null;

        // If type is SBO, set expiration to 1 year from now
        if ($reporterType && $reporterType->name === 'SBO') {
            $expirationDate = now()->addYear()->toDateString();
        }

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
            'type_id' => $request->type_id,
            'expiration_date' => $expirationDate,
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
