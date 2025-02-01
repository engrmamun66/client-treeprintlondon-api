<?php

namespace App\Http\Controllers\API;
  
use App\Http\Controllers\API\BaseController as BaseController;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\ProductColor;
use App\Models\ProductGender;
Use App\Http\Requests\ProductRequest;
use App\Traits\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
            $product = Product::with(['colors','sizes','genders','images'])->find($id);

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
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }
            if ($request->hasFile('thumbnail_image')) {
                
                $originalImage = $request->file('thumbnail_image');
                
                // Store the original image in public disk
                // $originalPath = $this->FileUpload($originalImage, 'product/original');

                // Store the thumbnail image (150x150) in public disk
                $thumbnailPath = $this->FileUpload($originalImage, 'product/thumbnail', [150, 150]);

                // $validatedData['original_image'] = $originalPath;
                $validatedData['thumbnail_image'] = $thumbnailPath;
            }
            $validatedData['status'] = $validatedData['status'] ?? true;

            $product = Product::create($validatedData);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $this->FileUpload($image, 'product/images'); // Call your custom upload function
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imagePath
                    ]);
                }
            }
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
                    if (isset($color)) {
                        ProductColor::create([
                            'product_id' => $product->id,
                            'color_id' => $color
                        ]);
                    }
                }
            }

            // Handle product colors
            $genders = json_decode($request->genders, true);
            if (is_array($genders)) {
                foreach ($genders as $gender) {
                    if (isset($gender)) {
                        ProductGender::create([
                            'product_id' => $product->id,
                            'gender_id' => $gender
                        ]);
                    }
                }
            }
            DB::commit();
            return $this->sendResponse($product, 'Product created successfully.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function update(ProductRequest $request, Product $product)
    {
        try {
            $validatedData = $request->validated();

            // Generate slug from the product name if it's updated
            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            // Handle thumbnail image upload
            if ($request->hasFile('thumbnail_image')) {
                // Delete the old thumbnail image if it exists
                if ($product->thumbnail_image) {
                    Storage::delete($product->thumbnail_image);
                }

                // Upload the new thumbnail image
                $thumbnailImage = $request->file('thumbnail_image');
                $thumbnailPath = $this->FileUpload($thumbnailImage, 'product/thumbnail', [150, 150]);

                $validatedData['thumbnail_image'] = $thumbnailPath;
            }

            // Set default status if not provided
            $validatedData['status'] = $validatedData['status'] ?? true;

            // Update the product with validated data
            $product->update($validatedData);

            // Handle additional images
            if ($request->hasFile('images')) {
                // Upload and save new additional images
                foreach ($request->file('images') as $image) {
                    $imagePath = $this->FileUpload($image, 'product/images');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imagePath,
                    ]);
                }
            }

            // Handle product sizes
            $sizes = json_decode($request->sizes, true);
            if (is_array($sizes)) {
                // Delete existing sizes for the product
                $product->sizes()->delete();

                // Create new sizes
                foreach ($sizes as $size) {
                    if (isset($size['id'], $size['quantity'], $size['unit_price'])) {
                        ProductSize::create([
                            'product_id' => $product->id,
                            'size_id' => $size['id'],
                            'quantity' => $size['quantity'],
                            'unit_price' => $size['unit_price'],
                        ]);
                    }
                }
            }

            // Handle product colors
            $colors = json_decode($request->colors, true);
            if (is_array($colors)) {
                // Delete existing colors for the product
                $product->colors()->delete();

                // Create new colors
                foreach ($colors as $color) {
                    if (isset($color)) {
                        ProductColor::create([
                            'product_id' => $product->id,
                            'color_id' => $color,
                        ]);
                    }
                }
            }

            // Handle product colors
            $genders = json_decode($request->genders, true);
            if (is_array($genders)) {
                // Delete existing colors for the product
                $product->genders()->delete();
                foreach ($genders as $gender) {
                    if (isset($gender)) {
                        ProductGender::create([
                            'product_id' => $product->id,
                            'gender_id' => $gender
                        ]);
                    }
                }
            }

            return $this->sendResponse($product, 'Product updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    public function deleteImage($id){
        $productImage = ProductImage::find($id);
        if (Storage::disk('public')->exists($productImage->image)) {
            Storage::disk('public')->delete($productImage->image);
        }
        $productImage->delete();
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
