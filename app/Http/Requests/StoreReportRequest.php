<?php

namespace App\Http\Requests;

use App\Rules\SecureFileUpload;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only reporters and security can submit reports
        return in_array($this->user()->user_type, [\App\Enums\UserType::Reporter, \App\Enums\UserType::Security]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'violation_type_id' => ['required', 'exists:violation_types,id'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'evidence_image' => ['required', 'file', new SecureFileUpload(['image/jpeg', 'image/png', 'image/jpg', 'image/heic', 'image/heif'], 5120, ['jpg', 'jpeg', 'png', 'heic', 'heif'], true)],
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
            'vehicle_id.required' => 'Please select a vehicle to report.',
            'vehicle_id.exists' => 'The selected vehicle does not exist.',
            'violation_type_id.required' => 'Please select a violation type.',
            'violation_type_id.exists' => 'The selected violation type is invalid.',
            'location.required' => 'Please specify the location of the violation.',
            'location.max' => 'Location description cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'evidence_image.required' => 'Evidence image is required.',
            'evidence_image.image' => 'The file must be an image.',
            'evidence_image.mimes' => 'Image must be a JPEG, PNG, HEIC, or HEIF file.',
            'evidence_image.max' => 'Image size cannot exceed 5MB.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Only reporters and security personnel can submit violation reports.'
        );
    }
}
