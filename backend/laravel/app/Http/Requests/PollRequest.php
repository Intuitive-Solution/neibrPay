<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PollRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'open']),
            ],
            'opens_at' => [
                'nullable',
                'date',
            ],
            'closes_at' => [
                'nullable',
                'date',
                // Only compare against opens_at when one was actually supplied
                Rule::when(
                    filled($this->input('opens_at')),
                    ['after:opens_at']
                ),
            ],
            'results_visibility' => [
                'required',
                Rule::in(['after_close', 'live', 'admins_only']),
            ],
            'questions' => [
                'required',
                'array',
                'min:1',
                'max:20',
            ],
            'questions.*.prompt' => [
                'required',
                'string',
                'max:255',
            ],
            'questions.*.type' => [
                'required',
                Rule::in(['single_choice', 'multi_select', 'yes_no']),
            ],
            'questions.*.options' => [
                'required',
                'array',
                'min:2',
            ],
            'questions.*.options.*.label' => [
                'required',
                'string',
                'max:255',
            ],
            'recipients' => [
                'required',
                'array',
                'min:1',
            ],
            'recipients.*.recipient_type' => [
                'required',
                'string',
                Rule::in(['all_units', 'unit']),
            ],
            'recipients.*.recipient_id' => [
                'nullable',
                'integer',
                'required_if:recipients.*.recipient_type,unit',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'title.max' => 'The title may not be greater than 255 characters.',

            'closes_at.after' => 'The close date must be after the open date.',

            'questions.required' => 'A poll needs at least one question.',
            'questions.min' => 'A poll needs at least one question.',
            'questions.max' => 'A poll can have at most 20 questions.',
            'questions.*.prompt.required' => 'Every question needs a prompt.',
            'questions.*.options.required' => 'Every question needs at least two options.',
            'questions.*.options.min' => 'Every question needs at least two options.',
            'questions.*.options.*.label.required' => 'Every option needs a label.',

            'recipients.required' => 'Choose who votes on this poll.',
            'recipients.min' => 'Choose who votes on this poll.',
            'recipients.*.recipient_id.required_if' => 'A unit must be selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'title',
            'description' => 'description',
            'opens_at' => 'open date',
            'closes_at' => 'close date',
            'results_visibility' => 'results visibility',
            'questions' => 'questions',
            'recipients' => 'audience',
        ];
    }
}
