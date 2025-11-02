<?php

namespace App\Http\Requests;

use App\Rules\SecureFileUpload;
use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only administrators can upload license images
        return in_array($this->user()->user_type, ['global_administrator', 'administrator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'license_image' => ['required', 'file', new SecureFileUpload(['image/jpeg', 'image/png', 'image/jpg', 'image/heic', 'image/heif'], 5120, ['jpg', 'jpeg', 'png', 'heic', 'heif'], true)],
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
            'license_image.required' => 'License image is required.',
            'license_image.file' => 'License image must be a valid file.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Only administrators can upload license images.'
        );
    }
}
