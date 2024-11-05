<?php

namespace App\Http\Controllers\API;
  
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
class CategoryController extends BaseController
{
 
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function store(CategoryRequest $request)
    {
        $validatedData = $request->validated(); // Get only the validated data
        $category = $this->categoryService->saveCategory($validatedData);
        return $this->sendResponse($category, 'Category created successfully.', 201);
    }
}
