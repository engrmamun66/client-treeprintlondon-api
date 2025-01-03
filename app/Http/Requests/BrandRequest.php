<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $brandId = $this->route('brand') ? $this->route('brand')->id : null;
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:brands,name,' . ($brandId ?? 'NULL') . ',id',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:brands,slug,' . ($brandId ?? 'NULL') . ',id',
            ],
            'image' => [
                'nullable',
                'image', 
                'mimes:jpeg,png,jpg,gif', 
                'max:2048', // Maximum file size: 2MB
            ],
            'status' => [
                'nullable',
                'boolean', // Must be true or false
            ],
        ];
    }
}
