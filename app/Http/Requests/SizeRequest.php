<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SizeRequest extends FormRequest
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
        $sizeId = $this->route('size') ? $this->route('size')->id : null;
        return [
            //
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:sizes,name,' . ($sizeId ?? 'NULL') . ',id',
            ],
            'status' => [
                'nullable',
                'boolean', // Must be true or false
            ],
        ];
    }
}
