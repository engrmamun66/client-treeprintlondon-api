<?php

namespace App\Http\Controllers\API;
  
use App\Http\Controllers\API\BaseController as BaseController;

use Illuminate\Http\Request;
use App\Models\Product;
Use App\Http\Requests\ProductRequest;
use App\Traits\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseController
{
    use FileUpload;

    public function index(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 15;
            $products = Product::orderBy('id', 'DESC')->paginate($perPage);

            return $this->sendResponse($products, 'Product list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->sendError('Product not found.', [], 404);
            }

            return $this->sendResponse($product, 'Product found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            $validatedData = $request->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }
            if ($request->hasFile('image')) {
                
                $originalImage = $request->file('image');
                
                // Store the original image in public disk
                $originalPath = $this->FileUpload($originalImage, 'product/original');

                // Store the thumbnail image (150x150) in public disk
                $thumbnailPath = $this->FileUpload($originalImage, 'product/thumbnail', [150, 150]);

                $validatedData['original_image'] = $originalPath;
                $validatedData['thumbnail_image'] = $thumbnailPath;
            }
            $validatedData['status'] = $validatedData['status'] ?? true;

            $product = Product::create($validatedData);
             // Handle product sizes
            $sizes = json_decode($request->sizes, true);
            if (is_array($sizes)) {
                foreach ($sizes as $size) {
                    if (isset($size['id'], $size['quantity'], $size['unit_price'])) {
                        ProductSize::create([
                            'product_id' => $product->id,
                            'size_id' => $size['id'],
                            'quantity' => $size['quantity'],
                            'unit_price' => $size['unit_price']
                        ]);
                    }
                }
            }

            // Handle product colors
            $colors = json_decode($request->colors, true);
            if (is_array($colors)) {
                foreach ($colors as $color) {
                    if (isset($color['id'])) {
                        ProductColor::create([
                            'product_id' => $product->id,
                            'color_id' => $color['id']
                        ]);
                    }
                }
            }

            return $this->sendResponse($product, 'Product created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function update(ProductRequest $request, Product $product)
    {
        try {
            $validatedData = $request->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            if ($request->hasFile('image')) {
                if ($product->original_image) {
                    Storage::delete($product->original_image);
                }
                if ($product->thumbnail_image) {
                    Storage::delete($product->thumbnail_image);
                }
    
                $originalImage = $request->file('image');

                // Store the original image in public disk
                $originalPath = $this->FileUpload($originalImage, 'product/original');

                // Store the thumbnail image (150x150) in public disk
                $thumbnailPath = $this->FileUpload($originalImage, 'product/thumbnail', [150, 150]);

                $validatedData['original_image'] = $originalPath;
                $validatedData['thumbnail_image'] = $thumbnailPath;
            }
            $product->update($validatedData);
            return $this->sendResponse($product, 'Product updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            if ($product->original_image) {
                Storage::delete($product->original_image);
            }
            if ($product->thumbnail_image) {
                Storage::delete($product->thumbnail_image);
            }

            $product->delete();

            return $this->sendResponse(null, 'Product deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
