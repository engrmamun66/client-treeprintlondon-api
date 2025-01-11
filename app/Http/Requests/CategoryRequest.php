<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category') ? $this->route('category')->id : null;
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:categories,name,' . ($categoryId ?? 'NULL') . ',id',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:categories,slug,' . ($categoryId ?? 'NULL') . ',id',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'image' => [
                'nullable',
                'image', 
                'mimes:jpeg,png,jpg,gif', 
                'max:2048', // Maximum file size: 2MB
            ],
            'parent_id' => [
                'nullable',
                'exists:categories,id', // Ensure parent category exists
            ],
            'is_popular_product' => [
                'nullable',
                'boolean', // Must be true or false
            ],
            'status' => [
                'nullable',
                'boolean', // Must be true or false
            ],
        ];

    }
}
