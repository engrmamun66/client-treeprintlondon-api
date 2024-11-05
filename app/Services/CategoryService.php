<?php
namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;
class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function saveCategory(array $data)
    {
          // Generate a slug from the name if it's not already present in $data
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $category = $this->categoryRepository->create($data);
        return $category;
    }
}