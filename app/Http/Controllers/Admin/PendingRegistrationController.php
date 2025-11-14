<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Events\ReporterUpdated;
use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationApprovedMail;
use App\Mail\RegistrationRejectedMail;
use App\Models\PendingRegistration;
use App\Models\Program;
use App\Models\Reporter;
use App\Models\Security;
use App\Models\Staff;
use App\Models\Stakeholder;
use App\Models\StakeholderType;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\PaymentBatchService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PendingRegistrationController extends Controller
{
    protected $stickerGenerator;

    public function __construct(StickerGenerator $stickerGenerator)
    {
        $this->stickerGenerator = $stickerGenerator;
    }

    /**
     * Display pending registrations.
     */
    public function index(Request $request)
    {
        // Only global administrators can access this
        if (Auth::user()->user_type !== UserType::GlobalAdministrator) {
            abort(403, 'Access denied. Global administrator privileges required.');
        }

        $query = PendingRegistration::with(['reviewer', 'reporterRole', 'pendingVehicles.vehicleType']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter - only pending registrations exist (approved/rejected are deleted)
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('status', 'pending');
            }
            // If status is 'approved' or 'rejected', return empty results since they're deleted
        } else {
            // Default to only showing pending registrations
            $query->where('status', 'pending');
        }

        // User type filter
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $perPage = $request->get('per_page', 20);
        $pendingRegistrations = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Get counts for statistics (approved/rejected are deleted, so only pending exist)
        $stats = [
            'pending' => PendingRegistration::where('status', 'pending')->count(),
            'approved' => 0, // Approved registrations are deleted after approval
            'rejected' => 0, // Rejected registrations are deleted after rejection
            'total' => PendingRegistration::where('status', 'pending')->count(),
        ];

        return view('admin.pending-registrations', [
            'pageTitle' => 'Pending Registrations',
            'pendingRegistrations' => $pendingRegistrations,
            'stats' => $stats,
        ]);
    }

    /**
     * Show a single pending registration.
     */
    public function show(PendingRegistration $pendingRegistration)
    {
        // Only global administrators can access this
        if (Auth::user()->user_type !== UserType::GlobalAdministrator) {
            abort(403, 'Access denied. Global administrator privileges required.');
        }

        $pendingRegistration->load(['reviewer', 'reporterRole', 'pendingVehicles.vehicleType', 'program', 'stakeholderType']);

        return response()->json([
            'registration' => [
                'id' => $pendingRegistration->id,
                'first_name' => $pendingRegistration->first_name,
                'last_name' => $pendingRegistration->last_name,
                'email' => $pendingRegistration->email,
                'user_type' => $pendingRegistration->user_type,
                'status' => $pendingRegistration->status,
                'rejection_reason' => $pendingRegistration->rejection_reason,
                'created_at' => $pendingRegistration->created_at,
                'reviewed_at' => $pendingRegistration->reviewed_at,
                'has_vehicle' => $pendingRegistration->has_vehicle,
                'vehicle_info' => $pendingRegistration->vehicle_info,
                'license_no' => $pendingRegistration->license_no,
                'license_image' => $pendingRegistration->license_image ? '/storage/'.$pendingRegistration->license_image : null,
                'student_id' => $pendingRegistration->student_id,
                'staff_id' => $pendingRegistration->staff_id,
                'security_id' => $pendingRegistration->security_id,
                'ip_address' => $pendingRegistration->ip_address,
                'user_agent' => $pendingRegistration->user_agent,
                'reporter_role' => $pendingRegistration->reporterRole ? [
                    'id' => $pendingRegistration->reporterRole->id,
                    'name' => $pendingRegistration->reporterRole->name,
                ] : null,
                'program' => $pendingRegistration->program ? [
                    'id' => $pendingRegistration->program->id,
                    'name' => $pendingRegistration->program->name,
                    'code' => $pendingRegistration->program->code,
                ] : null,
                'stakeholder_type' => $pendingRegistration->stakeholderType ? [
                    'id' => $pendingRegistration->stakeholderType->id,
                    'name' => $pendingRegistration->stakeholderType->name,
                ] : null,
                'pending_vehicles' => $pendingRegistration->pendingVehicles->map(function ($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'type_id' => $vehicle->type_id,
                        'plate_no' => $vehicle->plate_no,
                        'vehicle_type' => $vehicle->vehicleType ? [
                            'id' => $vehicle->vehicleType->id,
                            'name' => $vehicle->vehicleType->name,
                            'requires_plate' => $vehicle->vehicleType->requires_plate,
                        ] : null,
                    ];
                }),
                'reviewer' => $pendingRegistration->reviewer ? [
                    'id' => $pendingRegistration->reviewer->id,
                    'first_name' => $pendingRegistration->reviewer->first_name,
                    'last_name' => $pendingRegistration->reviewer->last_name,
                ] : null,
            ],
        ]);
    }

    /**
     * Approve a pending registration.
     */
    public function approve(Request $request, PendingRegistration $pendingRegistration)
    {
        // Only global administrators can approve
        if (Auth::user()->user_type !== UserType::GlobalAdministrator) {
            abort(403, 'Access denied. Global administrator privileges required.');
        }

        if ($pendingRegistration->status !== 'pending') {
            return back()->withErrors(['error' => 'This registration has already been processed.']);
        }

        DB::transaction(function () use ($pendingRegistration) {
            // Map string values to UserType enum
            $userTypeMap = [
                'student' => UserType::Student,
                'staff' => UserType::Staff,
                'stakeholder' => UserType::Stakeholder,
                'security' => UserType::Security,
                'reporter' => UserType::Reporter,
            ];

            // Create the user account
            $user = User::create([
                'first_name' => $pendingRegistration->first_name,
                'last_name' => $pendingRegistration->last_name,
                'email' => $pendingRegistration->email,
                'password' => $pendingRegistration->password, // Already hashed
                'user_type' => $userTypeMap[$pendingRegistration->user_type],
                'is_active' => true,
            ]);

            // Handle license image - copy from pending registration if exists
            $licenseImagePath = null;
            if ($pendingRegistration->license_image) {
                // Copy the license image from pending registration storage to licenses folder
                $sourcePath = storage_path('app/public/'.$pendingRegistration->license_image);
                if (file_exists($sourcePath)) {
                    $filename = time().'_'.uniqid().'.'.pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $licenseImagePath = 'licenses/'.$filename;
                    $destinationPath = storage_path('app/public/'.$licenseImagePath);
                    if (! is_dir(storage_path('app/public/licenses'))) {
                        mkdir(storage_path('app/public/licenses'), 0755, true);
                    }
                    copy($sourcePath, $destinationPath);
                }
            }

            // Create user type-specific record based on user_type
            $rules = \App\Models\StickerRule::getSingleton();
            $vehicleIds = [];
            $student = null;
            $staff = null;
            $stakeholder = null;
            $security = null;
            $reporter = null;

            switch ($pendingRegistration->user_type) {
                case 'student':
                    $program = Program::findOrFail($pendingRegistration->program_id);
                    $years = (int) ($rules->student_expiration_years ?? 4);
                    $student = Student::create([
                        'user_id' => $user->id,
                        'college_id' => $program->college_id,
                        'program_id' => $pendingRegistration->program_id,
                        'student_id' => $pendingRegistration->student_id,
                        'license_no' => $pendingRegistration->license_no,
                        'license_image' => $licenseImagePath,
                        'expiration_date' => now()->addYears($years),
                    ]);
                    break;

                case 'staff':
                    $years = (int) ($rules->staff_expiration_years ?? 4);
                    $staff = Staff::create([
                        'user_id' => $user->id,
                        'staff_id' => $pendingRegistration->staff_id,
                        'license_no' => $pendingRegistration->license_no,
                        'license_image' => $licenseImagePath,
                        'expiration_date' => now()->addYears($years),
                    ]);
                    break;

                case 'stakeholder':
                    $years = (int) ($rules->stakeholder_expiration_years ?? 4);
                    $stakeholder = Stakeholder::create([
                        'user_id' => $user->id,
                        'type_id' => $pendingRegistration->stakeholder_type_id,
                        'license_no' => $pendingRegistration->license_no,
                        'license_image' => $licenseImagePath,
                        'guardian_evidence' => $pendingRegistration->guardian_evidence,
                        'expiration_date' => now()->addYears($years),
                    ]);
                    break;

                case 'security':
                    $years = (int) ($rules->security_expiration_years ?? 4);
                    $security = Security::create([
                        'user_id' => $user->id,
                        'security_id' => $pendingRegistration->security_id,
                        'license_no' => $pendingRegistration->license_no,
                        'license_image' => $licenseImagePath,
                        'expiration_date' => now()->addYears($years),
                    ]);
                    break;

                case 'reporter':
                    $reporter = Reporter::create([
                        'user_id' => $user->id,
                        'reporter_role_id' => $pendingRegistration->reporter_role_id,
                        'is_active' => true,
                    ]);
                    // Broadcast reporter creation
                    broadcast(new ReporterUpdated($reporter, 'created'));
                    break;
            }

            // Create vehicles and generate stickers if provided
            if ($pendingRegistration->pendingVehicles->count() > 0) {
                foreach ($pendingRegistration->pendingVehicles as $pendingVehicle) {
                    $vehicleType = \App\Models\VehicleType::find($pendingVehicle->type_id);
                    $plateNumber = $pendingVehicle->plate_no;

                    // If vehicle type doesn't require plate, set to null
                    if ($vehicleType && ! $vehicleType->requires_plate) {
                        $plateNumber = null;
                    } else {
                        // For vehicles that require plate, ensure plate_no is not empty string
                        $plateNumber = ! empty($plateNumber) ? $plateNumber : null;
                    }

                    // Determine sticker color based on user type
                    $stickerColor = null;
                    $stickerTypeName = null;

                    switch ($pendingRegistration->user_type) {
                        case 'student':
                            $stickerColor = $this->stickerGenerator->determineStickerColor('student', null, $plateNumber);
                            $stickerTypeName = $vehicleType?->name ?? 'Vehicle';
                            break;

                        case 'staff':
                            $stickerColor = $this->stickerGenerator->determineStickerColor('staff', null, $plateNumber);
                            $stickerTypeName = 'staff';
                            break;

                        case 'stakeholder':
                            $stakeholderType = StakeholderType::find($stakeholder->type_id ?? 1);
                            $stakeholderTypeName = $stakeholderType ? $stakeholderType->name : 'Guardian';
                            $stickerColor = $this->stickerGenerator->determineStickerColor('stakeholder', $stakeholderTypeName, $plateNumber);
                            $stickerTypeName = 'stakeholder';
                            break;

                        case 'security':
                            $stickerColor = $this->stickerGenerator->determineStickerColor('security', null, $plateNumber);
                            $stickerTypeName = $vehicleType?->name ?? 'Vehicle';
                            break;

                        case 'reporter':
                            // Reporters don't have vehicles
                            continue 2;
                    }

                    // Generate sticker number
                    $stickerNumber = $this->stickerGenerator->generateNextStickerNumber($stickerColor);

                    // Create vehicle
                    $vehicle = Vehicle::create([
                        'user_id' => $user->id,
                        'type_id' => $pendingVehicle->type_id,
                        'plate_no' => $plateNumber,
                        'color' => $stickerColor,
                        'number' => $stickerNumber,
                        'is_active' => true,
                    ]);

                    // Generate sticker image
                    try {
                        $stickerPath = $this->stickerGenerator->generateVehicleSticker(
                            $stickerNumber,
                            $stickerTypeName,
                            $plateNumber,
                            $stickerColor,
                            $vehicle->id
                        );

                        $vehicle->update(['sticker' => $stickerPath]);
                    } catch (\Exception $e) {
                        \Log::error("Failed to generate sticker for vehicle {$vehicle->id}: ".$e->getMessage());
                        // Continue without sticker - it can be regenerated later
                    }

                    // Broadcast vehicle creation
                    $vehicle->load(['user', 'type']);
                    broadcast(new VehicleUpdated($vehicle, 'created', Auth::user()->first_name.' '.Auth::user()->last_name));

                    $vehicleIds[] = $vehicle->id;
                }

                // Create payment batches for vehicles (except for reporters who don't have vehicles)
                if (count($vehicleIds) > 0 && $pendingRegistration->user_type !== 'reporter') {
                    $batchSvc = app(PaymentBatchService::class);
                    $editorName = Auth::user()->first_name.' '.Auth::user()->last_name;
                    $batchSvc->addVehiclesToBatch($user->id, $vehicleIds, $editorName);
                }
            }

            // Broadcast user creation
            broadcast(new UserUpdated($user, 'created', 'admin'));

            // Log the approval before deletion
            \Log::channel('security')->info('Pending registration approved', [
                'pending_registration_id' => $pendingRegistration->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type->value,
                'vehicles_count' => $pendingRegistration->pendingVehicles->count(),
                'approved_by' => Auth::id(),
            ]);

            // Log activity for the new user
            \App\Services\ActivityLogService::log($user->id, 'registration_approved');

            // Broadcast pending registration approved event (before deletion)
            broadcast(new \App\Events\PendingRegistrationUpdated(
                $pendingRegistration,
                'approved',
                Auth::user()->first_name.' '.Auth::user()->last_name
            ));

            // Send approval email before deletion (so we can use the model object)
            try {
                Mail::to($pendingRegistration->email)->sendNow(new RegistrationApprovedMail($pendingRegistration));
            } catch (\Exception $e) {
                \Log::error('Failed to send registration approval email', [
                    'email' => $pendingRegistration->email,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the transaction if email fails
            }

            // Delete the pending registration from database
            $pendingRegistration->delete();
        });

        return back()->with('success', 'Registration approved successfully. User account and vehicle (if provided) have been created.');
    }

    /**
     * Reject a pending registration.
     */
    public function reject(Request $request, PendingRegistration $pendingRegistration)
    {
        // Only global administrators can reject
        if (Auth::user()->user_type !== UserType::GlobalAdministrator) {
            abort(403, 'Access denied. Global administrator privileges required.');
        }

        if ($pendingRegistration->status !== 'pending') {
            return back()->withErrors(['error' => 'This registration has already been processed.']);
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        // Capture email and data before deletion for sending notification
        $registrationEmail = $pendingRegistration->email;
        $registrationData = $pendingRegistration->toArray();
        $registrationData['rejection_reason'] = $request->rejection_reason;

        // Log the rejection before deletion
        \Log::channel('security')->info('Pending registration rejected', [
            'pending_registration_id' => $pendingRegistration->id,
            'email' => $registrationEmail,
            'user_type' => $pendingRegistration->user_type,
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => Auth::id(),
        ]);

        // Update status and rejection reason before broadcasting
        $pendingRegistration->status = 'rejected';
        $pendingRegistration->rejection_reason = $request->rejection_reason;
        $pendingRegistration->reviewed_at = now();
        $pendingRegistration->reviewed_by = Auth::id();
        $pendingRegistration->save();

        // Broadcast pending registration rejected event (before deletion)
        broadcast(new \App\Events\PendingRegistrationUpdated(
            $pendingRegistration,
            'rejected',
            Auth::user()->first_name.' '.Auth::user()->last_name
        ));

        // Delete the pending registration from database
        $pendingRegistration->delete();

        // Send rejection email (using captured data since pending registration is deleted)
        try {
            // Create a temporary object with the registration data for the email
            $tempRegistration = new PendingRegistration($registrationData);
            Mail::to($registrationEmail)->sendNow(new RegistrationRejectedMail($tempRegistration));
        } catch (\Exception $e) {
            \Log::error('Failed to send registration rejection email', [
                'email' => $registrationEmail,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Registration rejected successfully.');
    }

    /**
     * Delete a pending registration.
     */
    public function destroy(PendingRegistration $pendingRegistration)
    {
        // Only global administrators can delete
        if (Auth::user()->user_type !== UserType::GlobalAdministrator) {
            abort(403, 'Access denied. Global administrator privileges required.');
        }

        // Log the deletion
        \Log::channel('security')->info('Pending registration deleted', [
            'pending_registration_id' => $pendingRegistration->id,
            'email' => $pendingRegistration->email,
            'user_type' => $pendingRegistration->user_type,
            'status' => $pendingRegistration->status,
            'deleted_by' => Auth::id(),
        ]);

        // Broadcast pending registration deleted event (before deletion)
        broadcast(new \App\Events\PendingRegistrationUpdated(
            $pendingRegistration,
            'deleted',
            Auth::user()->first_name.' '.Auth::user()->last_name
        ));

        $pendingRegistration->delete();

        return back()->with('success', 'Pending registration deleted successfully.');
    }
}
