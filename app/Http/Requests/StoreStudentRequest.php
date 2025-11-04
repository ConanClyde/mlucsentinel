<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only global administrators and administrators can register students
        // user_type is an Enum, so we need to get its value
        $userType = $this->user()->user_type->value ?? $this->user()->user_type;

        return in_array($userType, ['global_administrator', 'administrator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[^\s@]+@(gmail\.com|student\.dmmmsu\.edu\.ph)$/',
            ],
            'program_id' => ['required', 'exists:programs,id'],
            'student_id' => [
                'required',
                'string',
                'max:255',
                'unique:students,student_id',
                'regex:/^2[0-9]{2}-[0-9]{4}-2$/',
            ],
            'license_no' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && \App\Models\Student::where('license_no', $value)->exists()) {
                        $fail('The license number has already been taken.');
                    }
                },
            ],
            'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,heic,heif', 'max:5120'],
            'vehicles' => ['required', 'array', 'min:1', 'max:3'],
            'vehicles.*.type_id' => ['required', 'exists:vehicle_types,id'],
            'vehicles.*.plate_no' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email is already registered.',
            'email.regex' => 'Email must be from Gmail (@gmail.com) or Student DMMMSU (@student.dmmmsu.edu.ph).',
            'student_id.unique' => 'Student ID is already registered.',
            'student_id.regex' => 'Student ID must be in format 2XX-XXXX-2 (e.g., 221-0238-2).',
            'license_no.unique' => 'License number is already registered.',
            'vehicles.required' => 'At least one vehicle is required.',
            'vehicles.min' => 'At least one vehicle is required.',
            'vehicles.max' => 'Maximum of 3 vehicles allowed per student.',
            'license_image.max' => 'License image size cannot exceed 2MB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate plate numbers based on vehicle type
            if ($this->has('vehicles')) {
                foreach ($this->vehicles as $index => $vehicle) {
                    $typeId = $vehicle['type_id'] ?? null;
                    $plateNo = $vehicle['plate_no'] ?? '';

                    // Get vehicle type to check if it requires plate number
                    $vehicleType = $typeId ? \App\Models\VehicleType::find($typeId) : null;

                    // Plate number is required if vehicle type requires it
                    if ($vehicleType && $vehicleType->requires_plate && empty($plateNo)) {
                        $validator->errors()->add(
                            "vehicles.{$index}.plate_no",
                            "Plate number is required for {$vehicleType->name} vehicles."
                        );
                    }

                    // Validate plate number format for vehicles that require plates
                    if ($vehicleType && $vehicleType->requires_plate && ! empty($plateNo)) {
                        if (! preg_match('/^[A-Z]{2,3}-[0-9]{3,4}$/', $plateNo)) {
                            $validator->errors()->add(
                                "vehicles.{$index}.plate_no",
                                'Plate number must be in format ABC-1234 (2-3 letters, dash, 3-4 numbers).'
                            );
                        }

                        // Check if plate number is unique
                        if (Vehicle::where('plate_no', $plateNo)->exists()) {
                            $validator->errors()->add(
                                "vehicles.{$index}.plate_no",
                                'Plate number is already registered.'
                            );
                        }
                    }
                }
            }
        });
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Only administrators can register students.'
        );
    }
}
