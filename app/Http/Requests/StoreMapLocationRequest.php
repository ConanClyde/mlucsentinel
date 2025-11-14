<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMapLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->user_type->value, ['global_administrator', 'administrator']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $locationId = $this->route('location') ? $this->route('location')->id : null;

        return [
            'type_id' => ['required', 'integer', 'exists:map_location_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_code' => ['nullable', 'string', 'max:5', 'unique:map_locations,short_code,'.$locationId],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'vertices' => ['nullable', 'array', 'min:3'], // Optional - only needed for map visualization
            'vertices.*.x' => ['required_with:vertices', 'numeric', 'min:0', 'max:100'],
            'vertices.*.y' => ['required_with:vertices', 'numeric', 'min:0', 'max:100'],
            'center_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'center_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'display_order' => ['integer', 'min:0'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'type_id.required' => 'Please select a location type.',
            'type_id.exists' => 'The selected location type is invalid.',
            'name.required' => 'Location name is required.',
            'short_code.unique' => 'This short code is already in use.',
            'color.regex' => 'Color must be a valid hex code (e.g., #3B82F6).',
            'vertices.min' => 'At least 3 points are required if drawing a polygon.',
            'vertices.*.x.required_with' => 'Each vertex must have an X coordinate.',
            'vertices.*.y.required_with' => 'Each vertex must have a Y coordinate.',
        ];
    }
}
