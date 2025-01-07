<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $productId = $this->route('product') ? $this->route('product')->id : null;
        return [

            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:products,name,' . ($productId ?? 'NULL') . ',id',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,slug,' . ($productId ?? 'NULL') . ',id',
            ],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,sku,' . ($productId ?? 'NULL') . ',id',
            ],
            'category_id' => [
                'required',
            ],
            'brand_id' => [
                'required',
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
            'status' => [
                'nullable',
                'boolean', // Must be true or false
            ],
        ];
    }
}
