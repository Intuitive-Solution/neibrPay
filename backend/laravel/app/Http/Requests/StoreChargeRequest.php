<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChargeRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0|max:999999.99',
            'budget_category_id' => 'required|exists:budget_categories,id',
            'is_active' => 'boolean',
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
            'title.required' => 'The charge title is required.',
            'title.max' => 'The charge title may not be greater than 255 characters.',
            'amount.required' => 'The charge amount is required.',
            'amount.numeric' => 'The charge amount must be a valid number.',
            'amount.min' => 'The charge amount must be at least 0.',
            'amount.max' => 'The charge amount may not be greater than 999,999.99.',
            'budget_category_id.required' => 'The budget category is required.',
            'budget_category_id.exists' => 'The selected budget category is invalid.',
        ];
    }
}
