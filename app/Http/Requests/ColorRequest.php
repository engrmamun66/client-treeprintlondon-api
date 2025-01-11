<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ColorRequest extends FormRequest
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
        $colorId = $this->route('color') ? $this->route('color')->id : null;
        return [
            //
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:colors,name,' . ($colorId ?? 'NULL') . ',id',
            ],
            'status' => [
                'nullable',
                'boolean', // Must be true or false
            ],
        ];
    }
}
