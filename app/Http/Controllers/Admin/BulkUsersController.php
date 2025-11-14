<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Events\StudentUpdated;
use App\Events\UserStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BulkUsersController extends Controller
{
    /**
     * Import users from CSV file.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // 10MB max
            'user_type' => ['required', 'string', Rule::in(['student', 'staff', 'security', 'stakeholder', 'reporter'])],
        ]);

        $file = $request->file('file');
        $userType = $request->input('user_type');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_shift($csvData); // Remove header row

        $errors = [];
        $successCount = 0;
        $skippedCount = 0;
        $processedRows = [];

        foreach ($csvData as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because we removed header and arrays are 0-indexed

            // Map CSV columns to data
            $data = array_combine($headers, $row);
            if (! $data) {
                $errors[] = "Row {$rowNumber}: Invalid column count";
                $skippedCount++;

                continue;
            }

            // Validate required fields based on user type
            $validator = $this->getValidationRules($userType, $data);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: ".implode(', ', $validator->errors()->all());
                $skippedCount++;

                continue;
            }

            try {
                DB::transaction(function () use ($data, $userType, &$successCount, $rowNumber, &$errors) {
                    // Check if user already exists
                    $existingUser = User::where('email', $data['email'])->first();
                    if ($existingUser) {
                        $errors[] = "Row {$rowNumber}: User with email {$data['email']} already exists";

                        return;
                    }

                    // Create user
                    $user = User::create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password'] ?? 'password123'), // Default password
                        'user_type' => UserType::from($userType),
                        'is_active' => isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                    ]);

                    // Create user type-specific record
                    $this->createUserTypeRecord($user, $userType, $data);

                    $successCount++;
                });
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                $skippedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Import completed: {$successCount} successful, {$skippedCount} skipped",
            'data' => [
                'success_count' => $successCount,
                'skipped_count' => $skippedCount,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Bulk update users.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'updates' => ['required', 'array'],
            'user_type' => ['required', 'string', Rule::in(['student', 'staff', 'security', 'stakeholder', 'reporter', 'administrator'])],
        ]);

        $userIds = $request->input('user_ids');
        $updates = $request->input('updates');
        $userType = $request->input('user_type');
        $updatedCount = 0;
        $errors = [];

        DB::transaction(function () use ($userIds, $updates, $userType, &$updatedCount, &$errors) {
            foreach ($userIds as $userId) {
                try {
                    $user = User::find($userId);
                    if (! $user || $user->user_type->value !== $userType) {
                        $errors[] = "User ID {$userId} not found or wrong type";

                        continue;
                    }

                    // Update user basic info
                    if (isset($updates['is_active'])) {
                        $oldIsActive = $user->is_active;
                        $user->is_active = filter_var($updates['is_active'], FILTER_VALIDATE_BOOLEAN);
                        $user->save();

                        // Broadcast status change
                        if ($oldIsActive !== $user->is_active) {
                            broadcast(new UserStatusChanged($user, $user->is_active));
                        }
                    }

                    // Update user type-specific fields
                    $this->updateUserTypeRecord($user, $userType, $updates);

                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "User ID {$userId}: {$e->getMessage()}";
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Updated {$updatedCount} user(s)",
            'data' => [
                'updated_count' => $updatedCount,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'user_type' => ['required', 'string', Rule::in(['student', 'staff', 'security', 'stakeholder', 'reporter', 'administrator'])],
        ]);

        $userIds = $request->input('user_ids');
        $userType = $request->input('user_type');
        $deletedCount = 0;
        $errors = [];
        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;

        DB::transaction(function () use ($userIds, $userType, &$deletedCount, &$errors) {
            foreach ($userIds as $userId) {
                try {
                    $user = User::find($userId);
                    if (! $user || $user->user_type->value !== $userType) {
                        $errors[] = "User ID {$userId} not found or wrong type";

                        continue;
                    }

                    // Broadcast deletion event for students
                    if ($userType === 'student' && $user->student) {
                        $student = $user->student;
                        broadcast(new StudentUpdated($student, 'deleted', auth()->user()));
                    }

                    // Delete user (cascade will handle related records)
                    $user->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "User ID {$userId}: {$e->getMessage()}";
                }
            }
        });

        // Notify administrators (exclude actor)
        if ($deletedCount > 0) {
            app(NotificationService::class)->notifyAdmins(
                'bulk_users_deleted',
                'Bulk Users Deleted',
                "{$editorName} deleted {$deletedCount} {$userType}(s)",
                [
                    'action' => 'bulk_deleted',
                    'count' => $deletedCount,
                    'user_type' => $userType,
                    'url' => '/users/'.($userType === 'student' ? 'students' : $userType),
                ],
                auth()->id()
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} user(s)",
            'data' => [
                'deleted_count' => $deletedCount,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Bulk status update (activate/deactivate).
     */
    public function bulkStatusUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
            'is_active' => ['required', 'boolean'],
            'user_type' => ['required', 'string', Rule::in(['student', 'staff', 'security', 'stakeholder', 'reporter', 'administrator'])],
        ]);

        $userIds = $request->input('user_ids');
        $isActive = $request->input('is_active');
        $userType = $request->input('user_type');
        $updatedCount = 0;
        $errors = [];

        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $action = $isActive ? 'activated' : 'deactivated';

        DB::transaction(function () use ($userIds, $isActive, $userType, &$updatedCount, &$errors) {
            foreach ($userIds as $userId) {
                try {
                    $user = User::find($userId);
                    if (! $user || $user->user_type->value !== $userType) {
                        $errors[] = "User ID {$userId} not found or wrong type";

                        continue;
                    }

                    $oldIsActive = $user->is_active;
                    $user->is_active = $isActive;
                    $user->save();

                    // Broadcast status change
                    if ($oldIsActive !== $isActive) {
                        broadcast(new UserStatusChanged($user, $isActive));

                        // Broadcast student update for real-time UI
                        if ($userType === 'student' && $user->student) {
                            broadcast(new StudentUpdated($user->student->fresh(['user', 'college', 'program']), 'updated', auth()->user()));
                        }
                    }

                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "User ID {$userId}: {$e->getMessage()}";
                }
            }
        });

        // Notify administrators (exclude actor)
        if ($updatedCount > 0) {
            app(NotificationService::class)->notifyAdmins(
                'bulk_users_status_updated',
                'Bulk Status Update',
                "{$editorName} {$action} {$updatedCount} {$userType}(s)",
                [
                    'action' => 'bulk_status_updated',
                    'count' => $updatedCount,
                    'user_type' => $userType,
                    'is_active' => $isActive,
                    'url' => '/users/'.($userType === 'student' ? 'students' : $userType),
                ],
                auth()->id()
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Updated status for {$updatedCount} user(s)",
            'data' => [
                'updated_count' => $updatedCount,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Get validation rules for CSV import based on user type.
     */
    private function getValidationRules(string $userType, array $data): \Illuminate\Validation\Validator
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
        ];

        if ($userType === 'student') {
            $rules['student_id'] = ['required', 'string', 'max:255', 'unique:students,student_id'];
            $rules['college_id'] = ['nullable', 'integer', 'exists:colleges,id'];
            $rules['program_id'] = ['nullable', 'integer', 'exists:programs,id'];
            $rules['license_no'] = ['nullable', 'string', 'max:255'];
        } elseif ($userType === 'staff') {
            $rules['staff_id'] = ['required', 'string', 'max:255', 'unique:staff,staff_id'];
            $rules['license_no'] = ['nullable', 'string', 'max:255'];
        }

        return Validator::make($data, $rules);
    }

    /**
     * Create user type-specific record.
     */
    private function createUserTypeRecord(User $user, string $userType, array $data): void
    {
        switch ($userType) {
            case 'student':
                $program = null;
                if (isset($data['program_id'])) {
                    $program = Program::find($data['program_id']);
                }

                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $data['student_id'],
                    'college_id' => $program?->college_id,
                    'program_id' => $data['program_id'] ?? null,
                    'license_no' => $data['license_no'] ?? null,
                ]);
                break;

            case 'staff':
                \App\Models\Staff::create([
                    'user_id' => $user->id,
                    'staff_id' => $data['staff_id'],
                    'license_no' => $data['license_no'] ?? null,
                ]);
                break;

            case 'security':
                \App\Models\Security::create([
                    'user_id' => $user->id,
                ]);
                break;

            case 'stakeholder':
                \App\Models\Stakeholder::create([
                    'user_id' => $user->id,
                    'stakeholder_type_id' => $data['stakeholder_type_id'] ?? null,
                ]);
                break;

            case 'reporter':
                \App\Models\Reporter::create([
                    'user_id' => $user->id,
                ]);
                break;
        }
    }

    /**
     * Update user type-specific record.
     */
    private function updateUserTypeRecord(User $user, string $userType, array $updates): void
    {
        switch ($userType) {
            case 'student':
                if ($user->student && (isset($updates['college_id']) || isset($updates['program_id']))) {
                    $student = $user->student;
                    if (isset($updates['program_id'])) {
                        $program = Program::find($updates['program_id']);
                        $student->program_id = $updates['program_id'];
                        $student->college_id = $program?->college_id;
                    } elseif (isset($updates['college_id'])) {
                        $student->college_id = $updates['college_id'];
                    }
                    $student->save();

                    broadcast(new StudentUpdated($student->fresh(['user', 'college', 'program']), 'updated', auth()->user()));
                }
                break;

            case 'staff':
                // Add staff-specific updates if needed
                break;
        }
    }
}
