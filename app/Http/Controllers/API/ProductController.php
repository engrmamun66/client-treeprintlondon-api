<?php

namespace App\Http\Controllers\API;
  
use App\Http\Controllers\API\BaseController as BaseController;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Gender;
use App\Models\Category;
use App\Models\DiscountLog;
use App\Models\DeliveryType;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\ProductColor;
use App\Models\ProductGender;
Use App\Http\Requests\ProductRequest;
use App\Traits\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class ProductController extends BaseController
{
    use FileUpload;

    public function index(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 20; // Number of items per page (default: 20)

            // Build the query with eager loading
            $query = Product::with(['category.parent', 'brand']);
            
            // Filter by type (via category_types)
            if ($typeId = $request->get('type_id')) {
                //$query->whereHas('category.types', function ($q) use ($typeId) {
                  //  $q->where('type_id', $typeId); // Use 'type_id' instead of 'types.id'
                //});
                $query->whereHas('category', function ($q) use ($typeId) {
                    $q->whereHas('types', function ($q) use ($typeId) {
                        $q->where('type_id', $typeId);
                    })->orWhereHas('parent.types', function ($q) use ($typeId) {
                        $q->where('type_id', $typeId);
                    });
                });
            }

            // Filter by category ID
            if ($categoryId = $request->get('category_id')) {
                $query->whereHas('category', function ($q) use ($categoryId) {
                    $q->where('id', $categoryId)
                    ->orWhere('parent_id', $categoryId); // Include subcategories
                });
            }

            // Filter by subcategory ID
            if ($subcategoryId = $request->get('subcategory_id')) {
                $query->whereHas('category', function ($q) use ($subcategoryId) {
                    $q->where('id', $subcategoryId);
                });
            }

            // Apply search filter if provided
            if ($search = $request->get('search')) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            // Order by ID (descending) and paginate
            $products = $query->latest('id')->paginate($perPage);

            return $this->sendResponse($products, 'Product list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            $search = $request->search; // Get the search term
    
            $products = Product::with(['category.parent', 'brand'])
                ->where('name', 'LIKE', "%$search%")
                ->orderBy('id', 'DESC')
                ->get(); // No pagination
    
            return $this->sendResponse($products, 'Product list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    
    public static function getAllChildCategoryIds(array $categoryIds): array
    {
        $allCategoryIds = $categoryIds;

        // Fetch immediate child categories
        $childCategories = Category::whereIn('parent_id', $categoryIds)->pluck('id');

        if ($childCategories->isNotEmpty()) {
            // Recursively fetch child categories of child categories
            $allCategoryIds = array_merge($allCategoryIds, $childCategories->toArray());
        }

        return $allCategoryIds;
    }

    public function filterProducts(Request $request)
    {
        try {
            $query = Product::query();

             // Filter by a single category slug
            if ($request->has('category_slug') && !empty($request->category_slug)) {
                // $query->whereHas('category', function ($q) use ($request) {
                //     $q->where('slug', $request->category_slug);
                // });
                $categoryIds = Category::where('slug', $request->category_slug)->pluck('id')->toArray();
                $allCategoryIds = Self::getAllChildCategoryIds($categoryIds);
            
                // Filter products by category IDs (including child categories)
                $query->whereIn('category_id', $allCategoryIds);
            }

            // Filter by category IDs
            if ($request->has('category_ids') && is_array($request->category_ids) && !empty($request->category_ids)) {
                // Fetch all child category IDs (including the given category IDs)
                $allCategoryIds = Self::getAllChildCategoryIds($request->category_ids);
            
                // Filter products by category IDs (including child categories)
                $query->whereIn('category_id', $allCategoryIds);
            }

            // Filter by brand IDs
            if ($request->has('brand_ids') && is_array($request->brand_ids) && !empty($request->brand_ids)) {
                $query->whereIn('brand_id', $request->brand_ids);
            }

            // Filter by sizes (assuming there is a pivot table like product_sizes)
            if ($request->has('size_ids') && is_array($request->size_ids) && !empty($request->size_ids)) {
                $query->whereHas('sizes', function ($q) use ($request) {
                    $q->whereIn('size_id', $request->size_ids);
                });
            }

            // Filter by genders
            if ($request->has('gender_ids') && is_array($request->gender_ids) && !empty($request->gender_ids)) {
                $query->whereHas('genders', function ($q) use ($request) {
                    $q->whereIn('gender_id', $request->gender_ids);
                });
            }

            // Search by product name (should be applied after other filters)
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }

            if ($request->has('min_unit_price') && $request->has('max_unit_price')) {
                $query->whereBetween('min_unit_price', [$request->min_unit_price, $request->max_unit_price]);
               
            }

            if ($request->has('sort')) {
                if ($request->sort == 'low') {
                    $query->orderBy('min_unit_price', 'ASC');
                } elseif ($request->sort == 'high') {
                    $query->orderBy('min_unit_price', 'DESC');
                }
            }
       

            // Get filtered results
            $products = $query->paginate($request->per_page ?? 20);

            return $this->sendResponse($products, 'Product list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with(['category.parent','colors','sizes','genders','images'])->find($id);

            if (!$product) {
                return $this->sendError('Product not found.', [], 404);
            }

            return $this->sendResponse($product, 'Product found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    function productDetailsBySlug($slug){
        try {
            $product = Product::with(['category.parent','colors','sizes','genders','images','brand'])->where('slug', $slug)->first();
            $typeId = 1;
            $query = Product::with(['category.parent'])
            ->whereHas('category.types', function ($q) use ($typeId) {
                $q->where('types.id', $typeId);
            })
            ->inRandomOrder()
            ->take(4)
            ->get();
            $categoryIds = [$product->category_id]; // Start with the product's category ID

            // Check if category has a parent, then add the parent category ID
            if ($product->category && $product->category->parent_id) {
                $categoryIds[] = $product->category->parent_id;
            }
        
            $relatedProducts =  Product::whereIn('category_id', $categoryIds)
                ->where('id', '!=', $product->id) // Exclude the current product
                ->inRandomOrder()
                ->take(4)
                ->get();
            if (!$product) {
                return $this->sendError('Product not found.', [], 404);
            }
            $data = [
                'product' => $product,
                'popular_products' => $query,
                'related_products' => $relatedProducts
   
            ];

            return $this->sendResponse($data, 'Product found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }

    }
    function additionalDataForProductFiltering(){
        try {
            $brands = Brand::orderBy('id', 'ASC')->get();
            $genders = Gender::orderBy('id', 'ASC')->get();
            $deliveryTypes = DeliveryType::orderBy('id', 'ASC')->get();
            $minPrice = Product::whereNotNull('min_unit_price')->min('min_unit_price');
            $maxPrice = Product::whereNotNull('min_unit_price')->max('min_unit_price');
            return $this->sendResponse(
                [
                    "brands" => $brands,
                    "genders" => $genders,
                    "min_price" => $minPrice,
                    "max_price" => $maxPrice,
                    'delivery_types' => $deliveryTypes
                ], 
                'Retrived data successfully.');
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
                $thumbnailPath = $this->FileUpload($originalImage, 'product/thumbnail', [400, 600]);

                // $validatedData['original_image'] = $originalPath;
                $validatedData['thumbnail_image'] = $thumbnailPath;
            }
            $validatedData['status'] = $validatedData['status'] ?? true;
            if(isset($validatedData['discount'])){
                $validatedData['discounted_min_unit_price'] = $validatedData['min_unit_price'] - ( $validatedData['min_unit_price'] * $validatedData['discount'] / 100 );
            }

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
                            'unit_price' => $size['unit_price'],
                            'discounted_unit_price' => $size['unit_price'] - ($size['unit_price'] * $product->discount / 100)
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
                $thumbnailPath = $this->FileUpload($thumbnailImage, 'product/thumbnail',  [400, 600]);

                $validatedData['thumbnail_image'] = $thumbnailPath;
            }

            // Set default status if not provided
            $validatedData['status'] = $validatedData['status'] ?? true;
            if(isset($validatedData['discount'])){
                $validatedData['discounted_min_unit_price'] = $validatedData['min_unit_price'] - ( $validatedData['min_unit_price'] * $validatedData['discount'] / 100 );
            }
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
                $product->sizes()->detach(); // Delete old sizes

                foreach ($sizes as $size) {
                    if (isset($size['id'], $size['quantity'], $size['unit_price'])) {
                        ProductSize::create([
                            'product_id' => $product->id,
                            'size_id' => $size['id'],
                            'quantity' => $size['quantity'],
                            'unit_price' => $size['unit_price'],
                            'discounted_unit_price' => $size['unit_price'] - ($size['unit_price'] * $product->discount / 100)
                        ]);
                    }
                }
            }

            // Handle product colors
            $colors = json_decode($request->colors, true);
            if (is_array($colors)) {
                $product->colors()->detach(); // Delete old colors

                foreach ($colors as $color) {
                    if (!empty($color)) {
                        ProductColor::create([
                            'product_id' => $product->id,
                            'color_id' => $color,
                        ]);
                    }
                }
            }

            // Handle product genders
            $genders = json_decode($request->genders, true);
            if (is_array($genders)) {
                $product->genders()->detach(); // Delete old genders

                foreach ($genders as $gender) {
                    if (!empty($gender)) {
                        ProductGender::create([
                            'product_id' => $product->id,
                            'gender_id' => $gender,
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
        return $this->sendResponse([], 'Image deleted successfully.');
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // Delete associated images
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }

            // Delete associated sizes, colors, and genders
            $product->sizes()->detach();
            $product->colors()->detach();
            $product->genders()->detach();

            // Delete product images
            if ($product->original_image && Storage::exists($product->original_image)) {
                Storage::disk('public')->delete($product->original_image);
            }
            if ($product->thumbnail_image && Storage::exists($product->thumbnail_image)) {
                Storage::disk('public')->delete($product->thumbnail_image);
            }

            // Delete the product
            $product->delete();

            DB::commit();
            return $this->sendResponse(null, 'Product deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    // public function applyDiscount(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'type' => 'required|in:category,all',
    //             'discount' => 'required|numeric|min:0|max:100',
    //             'category_id' => 'required_if:type,category|exists:categories,id'
    //         ]);
        
    //         if ($request->type === 'category') {
    //             Product::where('category_id', $request->category_id)->update([
    //                 'discount' => $request->discount,
    //                 'discounted_min_unit_price' => DB::raw('min_unit_price - (min_unit_price * ? / 100)', [$request->discount])
    //             ]);
    //         } else {
    //             Product::query()->update([
    //                 'discount' => $request->discount,
    //                 'discounted_min_unit_price' => DB::raw('min_unit_price - (min_unit_price * ? / 100)', [$request->discount])
    //             ]);
    //         }
    //         return $this->sendResponse(null, 'Discount added successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
    //     }
    // }

    
    public function discountApply(Request $request){
    
        
        try {
            // Initial validation
            $validated = $request->validate([
                'type' => 'required|in:category,all',
                'discount' => 'required|numeric|min:0|max:100',
                'category_id' => 'required_if:type,category'
            ]);
        
            // Conditional validation for category_id
            $validator = Validator::make($request->all(), []);
        
            $validator->sometimes('category_id', 'exists:categories,id', function ($input) {
                return $input->type === 'category' && !is_null($input->category_id);
            });
        
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors(),
                ], 422);
            }
            DB::beginTransaction();
            $query = Product::query();

            if ($request->type === 'category') {
                $allCategoryIds = Self::getAllChildCategoryIds([$request->category_id]);
                count( $allCategoryIds) > 0 ? $query->whereIn('category_id',  $allCategoryIds) : $query->where('category_id', $request->category_id );
            }

            // // Get affected product IDs before updating
            $affectedProducts = $query->pluck('id')->toArray() ?? [];

            // Update products in a single query
            $query->update([
                'discount' => $request->discount,
                'discounted_min_unit_price' => DB::raw('min_unit_price - (min_unit_price * ' . $request->discount . ' / 100)')
            ]);
            if (is_array($affectedProducts) && count($affectedProducts) > 0) {
                ProductSize::whereIn('product_id', $affectedProducts)
                ->update([
                    'discounted_unit_price' => DB::raw('unit_price - (unit_price * ' . $request->discount . ' / 100)')
                ]);
                 // Log the discount application
                DiscountLog::create([
                    'type' => $request->type,
                    'category_id' => $request->category_id ?? null,
                    'discount' => $request->discount,
                    'applied_to' => json_encode($affectedProducts),
                    'applied_at' => now(),
                ]);
            }
            DB::commit();
            return $this->sendResponse(null, 'Discount added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    public function getDiscountLogs(Request $request){
        try {
            $perPage = $request->get('per_page', 20); // Default to 10 items per page
            $discountLogs = DiscountLog::with(['category'])
                ->orderBy('applied_at', 'desc') // Sort by most recent discounts
                ->paginate($perPage);

            return $this->sendResponse($discountLogs, 'Discount logs fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
