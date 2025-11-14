<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Global administrators can update any report
        if ($user->user_type === UserType::GlobalAdministrator) {
            return true;
        }

        // Privilege-based: administrators with 'manage_reports' can update
        if (method_exists($user, 'hasPrivilege') && $user->hasPrivilege('manage_reports')) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
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
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status. Status must be pending, approved, or rejected.',
            'remarks.max' => 'Remarks cannot exceed 500 characters.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'You do not have permission to update report status.'
        );
    }
}
