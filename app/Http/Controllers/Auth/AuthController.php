<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\PendingRegistration;
use App\Models\PendingVehicle;
use App\Models\ReporterRole;
use App\Models\StakeholderType;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the landing page.
     */
    public function landing()
    {
        // Redirect authenticated users to home
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.landing');
    }

    /**
     * Show the registration form.
     */
    public function showRegister(Request $request)
    {
        // Redirect authenticated users to home
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $userType = $request->get('type');

        // If user type is specified, show specific registration form
        if ($userType && in_array($userType, ['student', 'staff', 'stakeholder', 'security', 'reporter'])) {
            $vehicleTypes = VehicleType::all();
            $reporterRoles = ReporterRole::where('is_active', true)->get();
            $colleges = College::with('programs')->get();
            $stakeholderTypes = StakeholderType::all();

            return view("auth.register.{$userType}", compact('vehicleTypes', 'reporterRoles', 'colleges', 'stakeholderTypes'));
        }

        // Otherwise show user type selection
        return view('auth.register.index');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        // Log the incoming request for debugging
        \Log::info('Registration attempt', [
            'user_type' => $request->user_type,
            'email' => $request->email,
            'has_vehicles' => $request->has('vehicles'),
            'is_ajax' => $request->expectsJson(),
        ]);

        $validationRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'unique:pending_registrations'],
            'user_type' => ['required', 'string', 'in:student,staff,stakeholder,security,reporter'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'license_no' => ['nullable', 'string', 'max:50'],
            'license_image' => ['nullable', 'image', 'max:2048'], // 2MB max
            'reporter_role_id' => ['nullable', 'exists:reporter_roles,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'student_id' => ['nullable', 'string', 'max:50', 'unique:pending_registrations'],
            'staff_id' => ['nullable', 'string', 'max:50', 'unique:pending_registrations'],
            'security_id' => ['nullable', 'string', 'max:50', 'unique:pending_registrations'],
            'stakeholder_type_id' => ['nullable', 'exists:stakeholder_types,id'],
        ];

        // Add student-specific validation
        if ($request->user_type === 'student') {
            $validationRules['program_id'] = ['required', 'exists:programs,id'];
            $validationRules['student_id'] = ['required', 'string', 'regex:/^2[0-9]{2}-[0-9]{4}-2$/', 'unique:pending_registrations'];
            $validationRules['email'] = ['required', 'string', 'email', 'max:255', 'regex:/^[^\s@]+@(gmail\.com|student\.dmmmsu\.edu\.ph)$/', 'unique:users', 'unique:pending_registrations'];
        }

        // Add staff-specific validation
        if ($request->user_type === 'staff') {
            $validationRules['staff_id'] = ['required', 'string', 'max:50', 'unique:pending_registrations', 'unique:staff,staff_id'];
        }

        // Add security-specific validation
        if ($request->user_type === 'security') {
            $validationRules['security_id'] = ['required', 'string', 'max:50', 'unique:pending_registrations', 'unique:security,security_id'];
        }

        // Add stakeholder-specific validation
        if ($request->user_type === 'stakeholder') {
            $validationRules['stakeholder_type_id'] = ['required', 'exists:stakeholder_types,id'];

            // Check if the selected stakeholder type requires evidence
            if ($request->stakeholder_type_id) {
                $stakeholderType = \App\Models\StakeholderType::find($request->stakeholder_type_id);
                if ($stakeholderType && $stakeholderType->evidence_required) {
                    $validationRules['guardian_evidence'] = ['required', 'image', 'mimes:jpeg,jpg,png', 'max:10240']; // 10MB max
                } else {
                    $validationRules['guardian_evidence'] = ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:10240']; // 10MB max
                }
            }
        }

        // Add vehicle validation if vehicles are provided
        if ($request->has('vehicles') && is_array($request->vehicles)) {
            $validationRules['vehicles'] = ['array', 'max:3']; // Maximum 3 vehicles
            $validationRules['vehicles.*.type_id'] = ['required', 'exists:vehicle_types,id'];
            $validationRules['vehicles.*.plate_no'] = ['nullable', 'string', 'max:20'];
        }

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Please check your form for errors.',
                ], 422);
            }
            throw $e;
        }

        // Handle license image upload
        $licenseImagePath = null;
        if ($request->hasFile('license_image')) {
            $licenseImagePath = $request->file('license_image')->store('license_images', 'public');
        }

        // Handle guardian evidence upload for stakeholders
        $guardianEvidencePath = null;
        if ($request->user_type === 'stakeholder' && $request->hasFile('guardian_evidence')) {
            $guardianEvidencePath = $request->file('guardian_evidence')->store('guardian_evidence', 'public');
        }

        // Create pending registration
        try {
            $pendingRegistration = PendingRegistration::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
                'license_no' => $request->license_no,
                'license_image' => $licenseImagePath,
                'guardian_evidence' => $guardianEvidencePath,
                'reporter_role_id' => $request->user_type === 'reporter' ? $request->reporter_role_id : null,
                'program_id' => $request->user_type === 'student' ? $request->program_id : null,
                'student_id' => $request->user_type === 'student' ? $request->student_id : null,
                'staff_id' => $request->user_type === 'staff' ? $request->staff_id : null,
                'security_id' => $request->user_type === 'security' ? $request->security_id : null,
                'stakeholder_type_id' => $request->user_type === 'stakeholder' ? $request->stakeholder_type_id : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create pending registration', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'user_type' => $request->user_type,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save registration. Please try again.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to save registration. Please try again.');
        }

        // Create pending vehicles if provided (not for reporters)
        if ($request->user_type !== 'reporter' && $request->has('vehicles') && is_array($request->vehicles)) {
            try {
                foreach ($request->vehicles as $vehicleData) {
                    if (! empty($vehicleData['type_id'])) {
                        PendingVehicle::create([
                            'pending_registration_id' => $pendingRegistration->id,
                            'type_id' => $vehicleData['type_id'],
                            'plate_no' => $vehicleData['plate_no'] ?? null,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to create pending vehicles', [
                    'error' => $e->getMessage(),
                    'pending_registration_id' => $pendingRegistration->id,
                    'vehicles' => $request->vehicles,
                ]);

                // Don't fail the entire registration for vehicle errors, just log them
            }
        }

        // Log pending registration
        \Log::info('New pending registration created', [
            'pending_registration_id' => $pendingRegistration->id,
            'email' => $pendingRegistration->email,
            'user_type' => $pendingRegistration->user_type,
            'vehicles_count' => $pendingRegistration->pendingVehicles()->count(),
            'ip_address' => $request->ip(),
            'success' => true,
        ]);

        // Broadcast pending registration creation event
        broadcast(new \App\Events\PendingRegistrationCreated($pendingRegistration));

        // Notify admins about new pending registration
        $notificationService = app(\App\Services\NotificationService::class);
        $userTypeName = ucfirst($pendingRegistration->user_type);
        $vehicleCount = $pendingRegistration->pendingVehicles()->count();
        $vehicleText = $vehicleCount > 0 ? " with {$vehicleCount} vehicle(s)" : '';

        $notificationService->notifyAdmins(
            'pending_registration',
            'New Pending Registration',
            "{$userTypeName} registration from {$pendingRegistration->first_name} {$pendingRegistration->last_name} ({$pendingRegistration->email}){$vehicleText}",
            [
                'pending_registration_id' => $pendingRegistration->id,
                'user_type' => $pendingRegistration->user_type,
                'email' => $pendingRegistration->email,
                'url' => route('admin.pending-registrations.show', $pendingRegistration->id),
            ]
        );

        // Return success response for AJAX handling
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration submitted successfully! Please wait for admin approval.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Registration submitted successfully! Your account is pending approval by an administrator.');
    }

    /**
     * Check email availability for registration.
     */
    public function checkEmailAvailability(Request $request)
    {
        $email = $request->input('email');

        if (! $email) {
            return response()->json([
                'available' => false,
                'message' => 'Email is required',
            ]);
        }

        // Check if email exists in users table
        $existsInUsers = User::where('email', $email)->exists();

        // Check if email exists in pending registrations
        $existsInPending = PendingRegistration::where('email', $email)->exists();

        if ($existsInUsers || $existsInPending) {
            return response()->json([
                'available' => false,
                'message' => 'This email address is already registered or pending approval',
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Email is available',
        ]);
    }

    /**
     * Check student ID availability for registration.
     */
    public function checkStudentIdAvailability(Request $request)
    {
        $studentId = $request->input('student_id');

        if (! $studentId) {
            return response()->json([
                'available' => false,
                'message' => 'Student ID is required',
            ]);
        }

        // For now, only check pending registrations since User model doesn't have student_id yet
        $existsInPending = PendingRegistration::where('student_id', $studentId)->exists();

        if ($existsInPending) {
            return response()->json([
                'available' => false,
                'message' => 'This Student ID is already registered or pending approval',
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Student ID is available',
        ]);
    }

    /**
     * Check staff ID availability for registration.
     */
    public function checkStaffIdAvailability(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|string',
        ]);

        $staffId = $request->staff_id;

        // Check if staff ID already exists in pending registrations
        $existsInPending = PendingRegistration::where('staff_id', $staffId)->exists();

        if ($existsInPending) {
            return response()->json([
                'available' => false,
                'message' => 'This staff ID is already registered by another pending user.',
            ]);
        }

        // Check if staff ID already exists in approved users (staff table)
        $existsInStaff = \App\Models\Staff::where('staff_id', $staffId)->exists();

        if ($existsInStaff) {
            return response()->json([
                'available' => false,
                'message' => 'This staff ID is already registered by another user.',
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Staff ID is available.',
        ]);
    }

    public function checkSecurityIdAvailability(Request $request)
    {
        $request->validate([
            'security_id' => 'required|string',
        ]);

        $securityId = $request->security_id;

        // Check if security ID already exists in pending registrations
        $existsInPending = PendingRegistration::where('security_id', $securityId)->exists();

        if ($existsInPending) {
            return response()->json([
                'available' => false,
                'message' => 'This security ID is already registered by another pending user.',
            ]);
        }

        // Check if security ID already exists in approved users (security table)
        $existsInSecurity = \App\Models\Security::where('security_id', $securityId)->exists();

        if ($existsInSecurity) {
            return response()->json([
                'available' => false,
                'message' => 'This security ID is already registered by another user.',
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Security ID is available.',
        ]);
    }

    /**
     * Check plate number availability for registration.
     */
    public function checkPlateNoAvailability(Request $request)
    {
        $request->validate([
            'plate_no' => 'required|string',
        ]);

        $plateNo = $request->plate_no;

        // Check if plate number already exists in pending registrations
        $existsInPending = PendingVehicle::where('plate_no', $plateNo)->exists();

        if ($existsInPending) {
            return response()->json([
                'available' => false,
                'message' => 'This plate number is already registered by another pending user.',
            ]);
        }

        // Check if plate number already exists in approved vehicles
        $existsInVehicles = \App\Models\Vehicle::where('plate_no', $plateNo)->exists();

        if ($existsInVehicles) {
            return response()->json([
                'available' => false,
                'message' => 'This plate number is already registered by another user.',
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Plate number is available.',
        ]);
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        // Redirect authenticated users to home
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is active
            if (! $user->is_active) {
                // Log inactive user login attempt
                \Log::channel('security')->warning('Inactive user attempted login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'user_type' => $user->user_type->value,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Logout the user
                Auth::logout();

                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact an administrator.',
                ])->onlyInput('email');
            }

            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                // Store user ID in session for 2FA verification
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:remember', $request->boolean('remember'));

                // Logout temporarily until 2FA is verified
                Auth::logout();

                // Redirect to 2FA verification page
                return redirect()->route('2fa.verify');
            }

            // Log successful login
            \Log::channel('security')->info('Successful login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type->value,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Log activity
            \App\Services\ActivityLogService::log($user->id, 'login');

            // Always redirect to home after login (ignore intended URL)
            return redirect()->route('home');
        }

        // Log failed login attempt
        \Log::channel('security')->warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log logout event
        if ($user) {
            \Log::channel('security')->info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type->value,
                'ip_address' => $request->ip(),
            ]);

            // Log activity
            \App\Services\ActivityLogService::log($user->id, 'logout');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('landing'))->with('success', 'You have been successfully logged out.');
    }
}
