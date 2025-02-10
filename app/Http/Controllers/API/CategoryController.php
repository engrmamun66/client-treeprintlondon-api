<?php

namespace App\Http\Controllers\API;
  
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Category;
Use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;
use App\Traits\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class CategoryController extends BaseController
{
    use FileUpload;

    public function index(Request $request)
    {
        try {
            // Get the 'perPage' parameter from the request, default to 10
            $perPage  = $request->per_page ?? 15;
            // Fetch paginated categories
            $categories = Category::with('parent')->orderBy('id', 'DESC')->paginate($perPage);
            // Return paginated response
            return $this->sendResponse($categories, 'Category list retrieved successfully.');

        } catch (Exception $e) {
            return $this->sendError($e, ["Line- ".$e->getLine().' '.$e->getMessage()], 500);
        }
    }
    public function parentCategoryDropdownList()
    {
        try {
            $categories = Category::select('id', 'name')
            ->whereNull('parent_id') // Correct column for parent categories
            ->get();
            // Return paginated response
            return $this->sendResponse($categories, 'Parent category list retrieved successfully.');

        } catch (Exception $e) {
            return $this->sendError($e, ["Line- ".$e->getLine().' '.$e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        try {
            // Find the category by ID
            $category = Category::with('children')->find($id);
    
            // Check if the category exists
            if (!$category) {
                return $this->sendError('Category not found.', [], 404);
            }
    
            // Return the category data
            return $this->sendResponse($category, 'Category found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function showCategoryDetailsByType($type)
    {
        try {
            // Find the category by ID
            $category = Category::with('children')->where('type', $type)->get();
    
            // Check if the category exists
            if (!$category) {
                return $this->sendError('Category not found.', [], 404);
            }
    
            // Return the category data
            return $this->sendResponse($category, 'Category found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function store(CategoryRequest $request)
    {
        try{
            $validatedData = $request->validated(); // Get only the validated data
            // Generate slug from the name if provided
            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            // Handle image upload if an image is provided
            if ($request->hasFile('image')) {
                // Store image in the storage/app/category_images directory
                $validatedData['image'] = $this->FileUpload($request->image,'category');
            }

            // Set default status if not explicitly provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = true; // Default to active
            }
            $category = Category::create($validatedData);

            $types = $validatedData['types'] ?? [];
            if (!empty($types)) {
                $category->types()->sync($types);
            }
            return $this->sendResponse($category, 'Category created successfully.', 201);

        } catch(\Exception $e){
            return $this->sendError($e, ["Line- ".$e->getLine().' '.$e->getMessage()], 500);
        }
    }
    public function update(CategoryRequest $request, Category $category)
    {
        try {
            $validatedData = $request->validated(); // Get only the validated data

            // Generate slug from the name if provided
            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            // Handle image upload if an image is provided
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($category->image) {
                    Storage::delete($category->image);
                }
                // Store the new image
                $validatedData['image'] = $this->FileUpload($request->image, 'category');
            }

            // Update the category with validated data
            $category->update($validatedData);

            return $this->sendResponse($category, 'Category updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    public function destroy(Category $category)
    {
        try {
            // Delete the associated image if it exists
            if ($category->image) {
                Storage::delete($category->image);
            }
            // Delete the category
            $category->delete();
            return $this->sendResponse(null, 'Category deleted successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
