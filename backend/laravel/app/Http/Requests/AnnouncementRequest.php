<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
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
        return [
            'subject' => [
                'required',
                'string',
                'max:255',
            ],
            'message' => [
                'required',
                'string',
            ],
            'removal_date' => [
                'nullable',
                'date',
                'after:today',
            ],
            'recipients' => [
                'required',
                'array',
                'min:1',
            ],
            'recipients.*.recipient_type' => [
                'required',
                'string',
                Rule::in(['all_members', 'all_admins', 'unit', 'resident']),
            ],
            'recipients.*.recipient_id' => [
                'nullable',
                'integer',
                'required_if:recipients.*.recipient_type,unit,resident',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'The subject is required.',
            'subject.max' => 'The subject may not be greater than 255 characters.',
            
            'message.required' => 'The message is required.',
            
            'removal_date.date' => 'Please enter a valid date.',
            'removal_date.after' => 'The removal date must be in the future.',
            
            'recipients.required' => 'At least one recipient must be selected.',
            'recipients.array' => 'Recipients must be an array.',
            'recipients.min' => 'At least one recipient must be selected.',
            
            'recipients.*.recipient_type.required' => 'Recipient type is required.',
            'recipients.*.recipient_type.in' => 'Invalid recipient type.',
            
            'recipients.*.recipient_id.required_if' => 'Recipient ID is required for unit or resident types.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'subject' => 'subject',
            'message' => 'message',
            'removal_date' => 'removal date',
            'recipients' => 'recipients',
        ];
    }
}
