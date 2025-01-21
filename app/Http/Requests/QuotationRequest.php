<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Update this to true if authorization is not needed for this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_of_service' => [
                'required',
                'integer',
                'in:1,2,3', // Ensures the value is 1, 2, or 3
            ],
            'delivery_date' => [
                'nullable',
                'date', // Ensures it is a valid date format
            ],
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'max:20', // Adjust max length based on expected phone format
            ],
            'requirements' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'files.*' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,gif,pdf', // Allowed file types
                'max:10000', // Maximum file size: 2MB
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'type_of_service.required' => 'The type of service is required.',
            'type_of_service.in' => 'The selected type of service is invalid.',
            'full_name.required' => 'The full name is required.',
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'phone.required' => 'The phone number is required.',
            'files.*.mimes' => 'Each file must be a file of type: jpeg, png, jpg, gif, pdf.',
            'files.*.max' => 'Each file may not be greater than 2MB.',
        ];
    }
}
