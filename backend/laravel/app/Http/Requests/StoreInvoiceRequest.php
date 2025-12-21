<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'integer|exists:units,id',
            'frequency' => 'required|in:one-time,monthly,weekly,quarterly,yearly',
            'start_date' => 'required|date|after_or_equal:today',
            'remaining_cycles' => 'nullable|string|in:endless,1,2,3,6,12,24',
            'due_date' => 'required|in:use_payment_terms,net_15,net_30,net_45,net_60,due_on_receipt',
            'early_payment_discount_enabled' => 'nullable|boolean',
            'early_payment_discount_amount' => 'nullable|numeric|min:0|max:999999.99|required_if:early_payment_discount_enabled,true',
            'early_payment_discount_type' => 'nullable|in:amount,percentage|required_if:early_payment_discount_enabled,true',
            'early_payment_discount_by_date' => 'nullable|date|required_if:early_payment_discount_enabled,true',
            'late_fee_enabled' => 'nullable|boolean',
            'late_fee_amount' => 'nullable|numeric|min:0|max:999999.99|required_if:late_fee_enabled,true',
            'late_fee_type' => 'nullable|in:amount,percentage|required_if:late_fee_enabled,true',
            'late_fee_applies_on_date' => 'nullable|date|required_if:late_fee_enabled,true',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.unit_cost' => 'required|numeric|min:0|max:999999.99',
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
            'items.*.line_total' => 'required|numeric|min:0|max:999999.99',
            'items.*.charge_id' => 'nullable|integer|exists:charges,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|array',
            'notes.public_notes' => 'nullable|string|max:10000',
            'notes.private_notes' => 'nullable|string|max:10000',
            'notes.terms' => 'nullable|string|max:10000',
            'notes.footer' => 'nullable|string|max:10000',
            'po_number' => 'nullable|string|max:255',
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
            'unit_ids.required' => 'Please select at least one unit.',
            'unit_ids.*.exists' => 'One or more selected units do not exist.',
            'frequency.required' => 'Please select a frequency.',
            'frequency.in' => 'Invalid frequency selected.',
            'start_date.required' => 'Please select a start date.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'remaining_cycles.in' => 'Invalid remaining cycles value.',
            'due_date.required' => 'Please select a due date option.',
            'due_date.in' => 'Invalid due date option.',
            'items.required' => 'Please add at least one item.',
            'items.min' => 'Please add at least one item.',
            'items.*.name.required' => 'Item name is required.',
            'items.*.name.max' => 'Item name cannot exceed 255 characters.',
            'items.*.description.max' => 'Item description cannot exceed 1000 characters.',
            'items.*.unit_cost.required' => 'Unit cost is required.',
            'items.*.unit_cost.numeric' => 'Unit cost must be a valid number.',
            'items.*.unit_cost.min' => 'Unit cost cannot be negative.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.numeric' => 'Quantity must be a valid number.',
            'items.*.quantity.min' => 'Quantity must be at least 0.01.',
            'items.*.line_total.required' => 'Line total is required.',
            'items.*.line_total.numeric' => 'Line total must be a valid number.',
            'items.*.line_total.min' => 'Line total cannot be negative.',
            'tax_rate.numeric' => 'Tax rate must be a valid number.',
            'tax_rate.min' => 'Tax rate cannot be negative.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'notes.public_notes.max' => 'Public notes cannot exceed 10000 characters.',
            'notes.private_notes.max' => 'Private notes cannot exceed 10000 characters.',
            'notes.terms.max' => 'Terms cannot exceed 10000 characters.',
            'notes.footer.max' => 'Footer cannot exceed 10000 characters.',
            'po_number.max' => 'PO number cannot exceed 255 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate that remaining_cycles is not required for one-time frequency
            if ($this->input('frequency') === 'one-time' && $this->input('remaining_cycles')) {
                $validator->errors()->add('remaining_cycles', 'Remaining cycles should not be set for one-time invoices.');
            }

            // Validate that remaining_cycles is required for recurring frequencies
            if ($this->input('frequency') !== 'one-time' && !$this->input('remaining_cycles')) {
                $validator->errors()->add('remaining_cycles', 'Remaining cycles is required for recurring invoices.');
            }

            // Validate line totals match unit cost * quantity
            if ($this->has('items')) {
                foreach ($this->input('items', []) as $index => $item) {
                    $expectedTotal = $item['unit_cost'] * $item['quantity'];
                    if (abs($item['line_total'] - $expectedTotal) > 0.01) {
                        $validator->errors()->add("items.{$index}.line_total", 
                            "Line total ({$item['line_total']}) does not match unit cost × quantity ({$expectedTotal}).");
                    }
                }
            }
        });
    }
}