<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only reporters and security can submit reports
        return in_array($this->user()->user_type, ['reporter', 'security']);
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
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'evidence_image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // 2MB max
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
            'description.required' => 'Please provide a description of the violation.',
            'description.min' => 'Description must be at least 10 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'evidence_image.required' => 'Evidence image is required.',
            'evidence_image.image' => 'The file must be an image.',
            'evidence_image.mimes' => 'Image must be a JPEG, PNG, or JPG file.',
            'evidence_image.max' => 'Image size cannot exceed 2MB.',
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
