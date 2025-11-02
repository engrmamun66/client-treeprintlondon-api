<?php
// app/Http/Requests/PostRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
        $productId = $this->route('post') ? $this->route('post')->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:posts,title,' . ($postId ?? 'NULL') . ',id',
            ],
            'content' => 'required|string|min:10',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10000',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date|after_or_equal:now',
            
            // SEO Meta fields
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10000',
            'canonical_url' => 'nullable|url|max:500',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'post title',
            'content' => 'post content',
            'excerpt' => 'post excerpt',
            'featured_image' => 'featured image',
            'meta_title' => 'meta title',
            'meta_description' => 'meta description',
            'meta_keywords' => 'meta keywords',
            'meta_image' => 'meta image',
            'canonical_url' => 'canonical URL',
        ];
    }

    
    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'title.unique' => 'A post with this title already exists.',
            'content.required' => 'The post content is required.',
            'content.min' => 'The post content must be at least 10 characters.',
            'featured_image.image' => 'The featured image must be a valid image file.',
            'featured_image.max' => 'The featured image must not exceed 2MB.',
            'meta_title.max' => 'The meta title must not exceed 60 characters.',
            'meta_description.max' => 'The meta description must not exceed 160 characters.',
            'meta_image.image' => 'The meta image must be a valid image file.',
            'meta_image.max' => 'The meta image must not exceed 2MB.',
            'canonical_url.url' => 'The canonical URL must be a valid URL.',
            'published_at.after_or_equal' => 'The publish date must be today or in the future.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided and title exists
        if ($this->has('title') && !$this->has('slug')) {
            $this->merge([
                'slug' => Str::slug($this->title)
            ]);
        }

        // Set default meta title if empty
        if ($this->has('title') && empty($this->meta_title)) {
            $this->merge([
                'meta_title' => $this->title
            ]);
        }

        // Set default meta description if empty but excerpt exists
        if (empty($this->meta_description) && !empty($this->excerpt)) {
            $this->merge([
                'meta_description' => Str::limit($this->excerpt, 160)
            ]);
        }

        // // Ensure boolean values
        // $this->merge([
        //     'is_published' => (bool) $this->is_published,
        // ]);

        $this->merge([
            'published_at' => now()
        ]);

        // Set published_at to now if is_published is true and no published_at provided
        // if ($this->is_published && empty($this->published_at)) {
        //     $this->merge([
        //         'published_at' => now()
        //     ]);
        // }
    }

    /**
     * Get the validated data from the request.
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();

        // Ensure slug is set
        if (empty($validated['slug']) && isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        return $validated;
    }
}