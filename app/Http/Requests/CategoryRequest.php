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
        $categoryId = $this->route('category'); // Assumes the route model binding or 'category' is the URL parameter name

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name,' . ($categoryId ?? 'NULL') . ',id',
            ],
            'slug' => [
                'required',
                'string',
                'unique:categories,slug,' . ($categoryId ?? 'NULL') . ',id',
            ],
        ];
    }
}
