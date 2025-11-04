<?php

namespace App\Http\Controllers\Admin\Users;

use App\Events\StudentUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use App\Services\PaymentBatchService;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    /**
     * Show the students page.
     */
    public function index()
    {
        // Get all students with their relationships
        $students = Student::with(['user', 'college', 'program', 'vehicles.type'])
            ->latest()
            ->paginate(10);

        $vehicleTypes = StaticDataCacheService::getVehicleTypes();
        $colleges = StaticDataCacheService::getColleges()->load('programs');

        return view('admin.users.students', [
            'pageTitle' => 'Students Management',
            'students' => $students,
            'vehicleTypes' => $vehicleTypes,
            'colleges' => $colleges,
        ]);
    }

    /**
     * Get students data for AJAX requests
     */
    public function data()
    {
        $students = Student::with(['user', 'college', 'program', 'vehicles.type'])
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                    'student_id' => $student->student_id,
                    'license_no' => $student->license_no,
                    'license_image' => $student->license_image ? '/storage/'.$student->license_image : null,
                    'college_id' => $student->college_id,
                    'program_id' => $student->program_id,
                    'user' => [
                        'id' => $student->user->id,
                        'first_name' => $student->user->first_name,
                        'last_name' => $student->user->last_name,
                        'email' => $student->user->email,
                        'is_active' => $student->user->is_active,
                    ],
                    'college' => $student->college ? [
                        'id' => $student->college->id,
                        'name' => $student->college->name,
                    ] : null,
                    'program' => $student->program ? [
                        'id' => $student->program->id,
                        'name' => $student->program->name,
                    ] : null,
                    'vehicles' => $student->vehicles->map(function ($vehicle) {
                        return [
                            'id' => $vehicle->id,
                            'type_id' => $vehicle->type_id,
                            'type_name' => $vehicle->type ? $vehicle->type->name : null,
                            'plate_no' => $vehicle->plate_no,
                            'color' => $vehicle->color,
                            'number' => $vehicle->number,
                            'sticker_image' => $vehicle->sticker,
                        ];
                    }),
                    'created_at' => $student->created_at,
                    'updated_at' => $student->updated_at,
                ];
            });

        return response()->json($students);
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student)
    {
        // Check authorization: Only Global Admin or Security Admin can update students
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$student->user_id],
            'student_id' => ['required', 'string', 'max:255', 'unique:students,student_id,'.$student->id],
            'license_no' => ['required', 'string', 'max:255', 'unique:students,license_no,'.$student->id],
            'program_id' => ['required', 'exists:programs,id'],
            'is_active' => ['required', 'boolean'],
            'vehicles' => ['nullable', 'array', 'max:3'],
            'vehicles.*.type_id' => ['required', 'exists:vehicle_types,id'],
            'vehicles.*.plate_no' => ['nullable', 'string', 'max:255'],
            'vehicles_to_delete' => ['nullable', 'array'],
            'vehicles_to_delete.*' => ['integer', 'exists:vehicles,id'],
            'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,heic,heif', 'max:5120'], // 5MB max
        ]);

        // Additional validation for plate numbers - check for duplicates
        if ($request->has('vehicles')) {
            $vehiclesArray = is_string($request->vehicles) ? json_decode($request->vehicles, true) : $request->vehicles;

            if ($vehiclesArray && ! empty($vehiclesArray)) {
                $plateNumbers = array_filter(array_column($vehiclesArray, 'plate_no'));
                $uniquePlateNumbers = array_unique($plateNumbers);

                if (count($plateNumbers) !== count($uniquePlateNumbers)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Duplicate plate numbers are not allowed.',
                    ], 422);
                }

                // Check if plate numbers already exist in the database (excluding soft-deleted vehicles)
                foreach ($vehiclesArray as $vehicleData) {
                    if (! empty($vehicleData['plate_no'])) {
                        $existingVehicle = \App\Models\Vehicle::withTrashed()
                            ->where('plate_no', $vehicleData['plate_no'])
                            ->where('user_id', '!=', $student->user_id)
                            ->first();

                        if ($existingVehicle) {
                            return response()->json([
                                'success' => false,
                                'message' => "Plate number '{$vehicleData['plate_no']}' is already registered to another user.",
                            ], 422);
                        }
                    }
                }
            }
        }

        DB::transaction(function () use ($validated, $student, $request) {
            // Get vehicles data from request (could be JSON string from FormData)
            $vehiclesData = $request->has('vehicles') ?
                (is_string($request->vehicles) ? json_decode($request->vehicles, true) : $request->vehicles) :
                $validated['vehicles'] ?? [];

            $vehiclesToDelete = $request->has('vehicles_to_delete') ?
                (is_string($request->vehicles_to_delete) ? json_decode($request->vehicles_to_delete, true) : $request->vehicles_to_delete) :
                [];

            // Store old is_active status
            $oldIsActive = $student->user->is_active;

            // Update user
            $student->user->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ]);

            // Broadcast status change directly to the user if is_active changed
            if ($oldIsActive !== $validated['is_active']) {
                broadcast(new \App\Events\UserStatusChanged($student->user, $validated['is_active']));
            }

            // Handle license image upload if provided
            $licenseImageData = [];
            if ($request->hasFile('license_image')) {
                // Delete old license image if exists
                if ($student->license_image) {
                    $oldPath = storage_path('app/public/'.$student->license_image);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                // Store new license image (without optimization to avoid GD dependency)
                $file = $request->file('license_image');
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('licenses', $filename, 'public');
                $licenseImageData['license_image'] = $path;
            }

            // Get program and its college
            $program = \App\Models\Program::findOrFail($validated['program_id']);

            // Update student
            $student->update(array_merge([
                'student_id' => $validated['student_id'],
                'license_no' => $validated['license_no'],
                'program_id' => $validated['program_id'],
                'college_id' => $program->college_id,
            ], $licenseImageData));

            // Delete vehicles marked for deletion
            if (! empty($vehiclesToDelete)) {
                $batchSvc = app(PaymentBatchService::class);
                $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                foreach ($vehiclesToDelete as $vehicleId) {
                    $vehicle = \App\Models\Vehicle::find($vehicleId);
                    if ($vehicle && $vehicle->user_id === $student->user_id) {
                        $batchSvc->removeVehicleFromBatch($vehicle->id, $editorName);
                        $vehicle->load(['user', 'type']);
                        broadcast(new VehicleUpdated($vehicle, 'deleted', $editorName));
                        $vehicle->delete();
                    }
                }
            }

            // Handle vehicles - only add new ones
            if (! empty($vehiclesData)) {
                // Add new vehicles only
                $stickerGenerator = new StickerGenerator;
                $newVehicleIds = [];

                foreach ($vehiclesData as $vehicleData) {
                    $plateNumber = $vehicleData['plate_no'] ?? null;
                    $typeId = $vehicleData['type_id'];

                    // Determine sticker color using StickerGenerator (uses StickerCounter)
                    $stickerColor = $stickerGenerator->determineStickerColor('student', null, $plateNumber);

                    // Generate sticker number using StickerCounter
                    $stickerNumber = $stickerGenerator->generateNextStickerNumber($stickerColor);

                    // Create vehicle first
                    $vehicle = $student->vehicles()->create([
                        'type_id' => $typeId,
                        'plate_no' => $plateNumber,
                        'color' => $stickerColor,
                        'number' => $stickerNumber, // Already generated from counter
                    ]);

                    // Generate sticker image
                    $stickerPath = $stickerGenerator->generateVehicleSticker(
                        $stickerNumber,
                        'student',
                        $plateNumber,
                        $stickerColor,
                        $vehicle->id
                    );

                    // Update vehicle with sticker data
                    $vehicle->update([
                        'number' => $stickerNumber,
                        'sticker' => $stickerPath,
                    ]);

                    // Broadcast vehicle creation
                    $vehicle->load(['user', 'type']);
                    broadcast(new VehicleUpdated($vehicle, 'created', auth()->user()->first_name.' '.auth()->user()->last_name));

                    // Track new vehicle IDs for payment creation
                    $newVehicleIds[] = $vehicle->id;
                }

                // Create pending payments for new vehicles
                if (count($newVehicleIds) > 0) {
                    $batchSvc = app(PaymentBatchService::class);
                    $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                    $batchSvc->addVehiclesToBatch($student->user_id, $newVehicleIds, $editorName);
                }
            }

            // Broadcast the event with fresh relationships
            broadcast(new StudentUpdated($student->fresh(['user', 'college', 'program', 'vehicles.type']), 'updated', auth()->user()));

            // Notify administrators (exclude actor)
            $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
            $studentName = $student->user->first_name.' '.$student->user->last_name;
            app(NotificationService::class)->notifyAdmins(
                'student_updated',
                'Student Updated',
                "{$editorName} updated Student {$studentName}",
                [
                    'student_id' => $student->id,
                    'action' => 'updated',
                    'url' => '/users/students',
                ],
                auth()->id()
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully!',
            'editor' => auth()->user()->first_name.' '.auth()->user()->last_name,
        ]);
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student)
    {
        // Check authorization: Only Global Admin or Security Admin can delete students
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $studentName = $student->user->first_name.' '.$student->user->last_name;

        DB::transaction(function () use ($student, $editorName, $studentName) {
            // Broadcast the event before deletion
            broadcast(new StudentUpdated($student, 'deleted', auth()->user()));

            // Notify administrators (exclude actor)
            app(NotificationService::class)->notifyAdmins(
                'student_deleted',
                'Student Removed',
                "{$editorName} removed {$studentName}",
                [
                    'action' => 'deleted',
                    'url' => '/users/students',
                ],
                auth()->id()
            );

            // Delete student and user
            $student->user->delete(); // This will cascade delete the student and vehicles
        });

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully!',
        ]);
    }
}
