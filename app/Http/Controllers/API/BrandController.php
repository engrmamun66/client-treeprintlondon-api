<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Brand;
use App\Http\Requests\BrandRequest;
use App\Traits\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends BaseController
{
    use FileUpload;

    public function index(Request $request)
    {
        try {
            // Get the 'perPage' parameter from the request, default to 15
            $perPage = $request->per_page ?? 15;

            // Fetch paginated brands
            $brands = Brand::orderBy('id', 'DESC')->paginate($perPage);

            // Return paginated response
            return $this->sendResponse($brands, 'Brand list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            // Find the brand by ID
            $brand = Brand::find($id);

            // Check if the brand exists
            if (!$brand) {
                return $this->sendError('Brand not found.', [], 404);
            }

            // Return the brand data
            return $this->sendResponse($brand, 'Brand found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function store(BrandRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Generate slug from the name if provided
            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            // Handle image upload if an image is provided
            if ($request->hasFile('image')) {
                $validatedData['image'] = $this->FileUpload($request->image, 'brand');
            }

            // Set default status if not explicitly provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = true; // Default to active
            }

            $brand = Brand::create($validatedData);

            return $this->sendResponse($brand, 'Brand created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        try {
            $validatedData = $request->validated();

            // Generate slug from the name if provided
            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            // Handle image upload if an image is provided
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($brand->image) {
                    Storage::delete($brand->image);
                }
                $validatedData['image'] = $this->FileUpload($request->image, 'brand');
            }

            // Update the brand with validated data
            $brand->update($validatedData);

            return $this->sendResponse($brand, 'Brand updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Brand $brand)
    {
        try {
            // Delete the associated image if it exists
            if ($brand->image) {
                Storage::delete($brand->image);
            }

            // Delete the brand
            $brand->delete();
            return $this->sendResponse(null, 'Brand deleted successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
