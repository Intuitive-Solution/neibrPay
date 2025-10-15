<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:maintenance,landscaping,legal,insurance,utilities,other',
            'ein' => 'nullable|string|max:20',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2|in:AL,AK,AZ,AR,CA,CO,CT,DE,FL,GA,HI,ID,IL,IN,IA,KS,KY,LA,ME,MD,MA,MI,MN,MS,MO,MT,NE,NV,NH,NJ,NM,NY,NC,ND,OH,OK,OR,PA,RI,SC,SD,TN,TX,UT,VT,VA,WA,WV,WI,WY,DC',
            'zip_code' => 'required|string|regex:/^\d{5}(-\d{4})?$/',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
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
            'name.required' => 'The vendor name is required.',
            'name.max' => 'The vendor name may not be greater than 255 characters.',
            'category.required' => 'The vendor category is required.',
            'category.in' => 'The selected category is invalid.',
            'ein.max' => 'The EIN may not be greater than 20 characters.',
            'street_address.required' => 'The street address is required.',
            'street_address.max' => 'The street address may not be greater than 255 characters.',
            'city.required' => 'The city is required.',
            'city.max' => 'The city may not be greater than 100 characters.',
            'state.required' => 'The state is required.',
            'state.size' => 'The state must be exactly 2 characters.',
            'state.in' => 'The selected state is invalid.',
            'zip_code.required' => 'The ZIP code is required.',
            'zip_code.regex' => 'The ZIP code format is invalid. Use 12345 or 12345-6789.',
            'website.url' => 'The website must be a valid URL.',
            'website.max' => 'The website may not be greater than 255 characters.',
            'contact_name.required' => 'The contact name is required.',
            'contact_name.max' => 'The contact name may not be greater than 255 characters.',
            'contact_email.required' => 'The contact email is required.',
            'contact_email.email' => 'The contact email must be a valid email address.',
            'contact_email.max' => 'The contact email may not be greater than 255 characters.',
            'contact_phone.required' => 'The contact phone is required.',
            'contact_phone.regex' => 'The contact phone format is invalid. Use (123) 456-7890.',
        ];
    }
}