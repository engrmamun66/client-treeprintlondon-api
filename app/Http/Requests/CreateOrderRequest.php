<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Set to true to allow all users to create orders
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'customer_first_name' => 'required|string|max:255',
            'customer_last_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string',
            'delivery_type_id' => 'required|exists:delivery_types,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1', // Ensure at least one item is present
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_size_id' => 'nullable|exists:product_sizes,id',
            'items.*.product_color_id' => 'nullable|exists:product_colors,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one order item is required.',
            'items.*.product_id.exists' => 'The selected product is invalid.',
            'items.*.product_size_id.exists' => 'The selected product size is invalid.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
            'items.*.unit_price.min' => 'The unit price must be at least 0.',
        ];
    }
}