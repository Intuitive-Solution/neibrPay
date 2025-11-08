<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get the resident ID from the route parameter
        // apiResource creates routes like PUT /residents/{resident}
        // Try multiple parameter names to be safe
        $residentId = $this->route('resident') 
                   ?? $this->route('id')
                   ?? $this->route()->parameter('resident')
                   ?? $this->route()->parameter('id');
        
        // If route parameter is a model instance, get its ID
        if (is_object($residentId) && method_exists($residentId, 'getKey')) {
            $residentId = $residentId->getKey();
        }
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($residentId)
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^(\(\d{3}\) \d{3}-\d{4}|\d{3}-\d{3}-\d{4})$/',
                'max:14'
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['owner', 'tenant', 'others'])
            ],
            'member_role' => [
                'required',
                'string',
                Rule::in(['admin', 'member'])
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The resident name is required.',
            'name.min' => 'The resident name must be at least 2 characters.',
            'name.max' => 'The resident name may not be greater than 255 characters.',
            
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'email.max' => 'The email address may not be greater than 255 characters.',
            
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'Please enter a valid US phone number (10 digits).',
            'phone.max' => 'The phone number may not be greater than 14 characters.',
            
            'type.required' => 'The resident type is required.',
            'type.in' => 'The resident type must be Owner, Tenant, or Others.',
            
            'member_role.required' => 'The member role is required.',
            'member_role.in' => 'The member role must be Admin or Member.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'resident name',
            'email' => 'email address',
            'phone' => 'phone number',
            'type' => 'resident type',
            'member_role' => 'member role',
        ];
    }
}
