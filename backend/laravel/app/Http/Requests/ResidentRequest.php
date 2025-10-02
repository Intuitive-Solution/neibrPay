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
        $residentId = $this->route('resident');
        
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
                'regex:/^\(\d{3}\) \d{3}-\d{4}$/',
                'max:14'
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
            'phone.regex' => 'Please enter a valid US phone number in the format (XXX) XXX-XXXX.',
            'phone.max' => 'The phone number may not be greater than 14 characters.',
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
        ];
    }
}
