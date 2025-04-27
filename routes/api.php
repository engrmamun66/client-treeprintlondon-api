<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ColorController;
use App\Http\Controllers\API\SizeController;
use App\Http\Controllers\API\QuotationController;
use App\Http\Controllers\API\GenderController;
use App\Http\Controllers\API\TypeController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\CouponController;

//public
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
});

Route::group([
    'middleware' => ['api'],
], function () {
    Route::get('/type-wise-category-list', [TypeController::class, 'getTypewiseCategoryList']); // Get all categories
    Route::post('/filter-products', [ProductController::class, 'filterProducts']); 
    Route::get('/search-products', [ProductController::class, 'search']);
    Route::post('/send-contact-us-email', [HomeController::class, 'submitContactForm']);
    Route::get('/dashboard-data', [HomeController::class, 'dashBoardData']);
    Route::post('/apply-coupon', [CouponController::class, 'applyCoupon']);
    
});

Route::group([
    'middleware' => ['api'],
    'prefix' => 'quotations'
], function () {
    Route::post('/', [QuotationController::class, 'store']); // Create a new category
   
});




// Route::group([
//     'middleware' => ['api'],
//     'prefix' => 'categories'
// ], function () {
//     Route::get('/type/{type}', [CategoryController::class, 'showCategoryDetailsByType']); // Create a new category
   
// });
Route::group([
    'middleware' => ['api']
   
], function () {
    // Get all categories
    Route::get('product-details-by-slug/{slug}', [ProductController::class, 'productDetailsBySlug']); // Get a specific category
    Route::get('category-details-by-slug/{slug}', [CategoryController::class, 'categoryDetailsBySlug']); // Get a specific category
    Route::get('additional-data-for-product-filtering', [ProductController::class, 'additionalDataForProductFiltering']);
    

});


Route::group([
    'middleware' => ['api'],
    'prefix' => 'orders'
], function () {
    Route::post('/', [OrderController::class, 'store']);
});

Route::group([
    'middleware' => ['api', 'auth:api'],
], function () {
    Route::post('/update-order-status', [OrderController::class, 'updateOrderStatus']);
});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'orders'
], function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{order_number}', [OrderController::class, 'show']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
});

// Route::group([
//     'middleware' => ['api'],
//     'prefix' => 'stripe'
// ], function () {
//     Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);
//     Route::post('/webhook', [PaymentController::class, 'handleWebhook']);
// });
Route::post('/create-payment', [PaymentController::class, 'createPayment']);
Route::get('/payment-success', [PaymentController::class, 'paymentSuccess'])->name('paypal.success');
Route::get('/payment-cancel', [PaymentController::class, 'paymentCancel'])->name('paypal.cancel');
//private


Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'categories'
], function () {
    Route::get('/', [CategoryController::class, 'index']); // Get all categories
    Route::get('/parent-category-list', [CategoryController::class, 'parentCategoryDropdownList']); // Get all categories
    Route::post('/', [CategoryController::class, 'store']); // Create a new category
    Route::get('/{category}', [CategoryController::class, 'show']); // Get a specific category
    Route::put('/{category}', [CategoryController::class, 'update']); // Update a specific category
    Route::delete('/{category}', [CategoryController::class, 'destroy']); // Delete a specific category
});
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'products'
], function () {
    Route::get('/', [ProductController::class, 'index']); // Get all categories
    Route::post('/', [ProductController::class, 'store']); // Create a new category
    Route::get('/{product}', [ProductController::class, 'show']); // Get a specific category
    Route::put('/{product}', [ProductController::class, 'update']); // Update a specific category
    Route::delete('/{product}', [ProductController::class, 'destroy']); // Delete a specific category
    Route::get('/image/{id}/delete', [ProductController::class, 'deleteImage']);
    
});

Route::group([
    'middleware' => ['api', 'auth:api']
], function () {
    Route::get('/discount-logs', [ProductController::class, 'getDiscountLogs']);
    Route::post('/apply-discount', [ProductController::class, 'discountApply']);
});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'brands'
], function () {
    Route::get('/', [BrandController::class, 'index']); // Get all categories
    Route::post('/', [BrandController::class, 'store']); // Create a new category
    Route::get('/{brand}', [BrandController::class, 'show']); // Get a specific category
    Route::put('/{brand}', [BrandController::class, 'update']); // Update a specific category
    Route::delete('/{brand}', [BrandController::class, 'destroy']); // Delete a specific category
});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'colors'
], function () {
    Route::get('/', [ColorController::class, 'index']); // Get all categories
    Route::post('/', [ColorController::class, 'store']); // Create a new category
    Route::get('/{color}', [ColorController::class, 'show']); // Get a specific category
    Route::put('/{color}', [ColorController::class, 'update']); // Update a specific category
    Route::delete('/{color}', [ColorController::class, 'destroy']); // Delete a specific category
});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'sizes'
], function () {
    Route::get('/', [SizeController::class, 'index']); // Get all categories
    // Route::post('/', [SizeController::class, 'store']); // Create a new category
    // Route::get('/{size}', [SizeController::class, 'show']); // Get a specific category
    // Route::put('/{size}', [SizeController::class, 'update']); // Update a specific category
    // Route::delete('/{size}', [SizeController::class, 'destroy']); // Delete a specific category
});
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'genders'
], function () {
    Route::get('/', [GenderController::class, 'index']); // Get all categories
});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'types'
], function () {
    Route::get('/', [TypeController::class, 'index']); // Get all categories
});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'quotations'
], function () {
    Route::get('/', [QuotationController::class, 'index']); // Get all categories
    Route::get('/{quotation}', [QuotationController::class, 'show']); // Get a specific category
    Route::put('/{quotation}', [QuotationController::class, 'update']); // Get a specific category
    Route::delete('/{quotation}', [QuotationController::class, 'destroy']); // Delete a specific category
    Route::get('/files/{id}/download', [QuotationController::class, 'downloadFile']);
});

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'coupons'
], function () {
    Route::get('/', [CouponController::class, 'index']); // List coupons
    Route::post('/', [CouponController::class, 'store']); // Create coupon
    Route::get('/{coupon}', [CouponController::class, 'show']); // Get single coupon
    Route::put('/{coupon}', [CouponController::class, 'update']); // Update coupon
    Route::delete('/{coupon}', [CouponController::class, 'destroy']); // Delete coupon

});

