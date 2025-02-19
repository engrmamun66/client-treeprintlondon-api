<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    // Constants for repeated values
    const MAX_IMAGE_SIZE = 10000; // 2MB
    const MAX_FILE_SIZE = 10000; // 10MB
    const ALLOWED_IMAGE_MIMES = 'jpeg,png,jpg,gif';
    const ALLOWED_FILE_MIMES = 'jpeg,png,jpg';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming all users are authorized to make this request
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
            // Product Name
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:products,name,' . ($productId ?? 'NULL') . ',id',
            ],

            // Slug
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,slug,' . ($productId ?? 'NULL') . ',id',
            ],

            // // SKU
            'discount' => [
                'nullable',
            ],
            'min_unit_price' => [
                'required'
            ],

            // Category and Brand
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],

            // Descriptions
            'short_description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'long_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            // Main Image
            'thumbnail_image' => [
                'nullable',
                'image',
                'mimes:' . self::ALLOWED_IMAGE_MIMES,
                'max:' . self::MAX_IMAGE_SIZE,
            ],

            // Status
            'status' => [
                'nullable',
                'boolean',
            ],

            // Sizes and Colors
            'sizes' => ['nullable', 'json'],
            'colors' => ['nullable', 'json'],

            // Additional Images
            'images.*' => [
                'nullable',
                'file',
                'mimes:' . self::ALLOWED_FILE_MIMES,
                'max:' . self::MAX_FILE_SIZE,
            ],
        ];
    }

    /**
     * Get custom validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'name.unique' => 'The product name must be unique.',
            'name.min' => 'The product name must be at least :min characters.',
            'name.max' => 'The product name may not be greater than :max characters.',

            'slug.unique' => 'The slug must be unique.',
            'slug.max' => 'The slug may not be greater than :max characters.',

            // 'sku.unique' => 'The SKU must be unique.',
            // 'sku.max' => 'The SKU may not be greater than :max characters.',

            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category is invalid.',

            'brand_id.required' => 'The brand is required.',
            'brand_id.exists' => 'The selected brand is invalid.',

            'short_description.max' => 'The short description may not be greater than :max characters.',
            'long_description.max' => 'The long description may not be greater than :max characters.',

            'thumbnail_image.image' => 'The file must be an image.',
            'thumbnail_image.mimes' => 'The image must be of type: ' . self::ALLOWED_IMAGE_MIMES . '.',
            'thumbnail_image.max' => 'The image may not be greater than ' . self::MAX_IMAGE_SIZE . ' KB.',

            'images.*.mimes' => 'The additional images must be of type: ' . self::ALLOWED_FILE_MIMES . '.',
            'images.*.max' => 'The additional images may not be greater than ' . self::MAX_FILE_SIZE . ' KB.',

            'min_unit_price.required' => 'The minimum unit price  is required.',
        ];
    }

}