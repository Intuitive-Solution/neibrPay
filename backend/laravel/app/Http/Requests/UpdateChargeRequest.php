<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChargeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0|max:999999.99',
            'category' => 'sometimes|in:hoa_fee,maintenance,penalties,special_assessment,other',
            'is_active' => 'sometimes|boolean',
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
            'title.max' => 'The charge title may not be greater than 255 characters.',
            'amount.numeric' => 'The charge amount must be a valid number.',
            'amount.min' => 'The charge amount must be at least 0.',
            'amount.max' => 'The charge amount may not be greater than 999,999.99.',
            'category.in' => 'The selected category is invalid.',
        ];
    }
}
