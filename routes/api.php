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
    'prefix' => 'products'
], function () {
    Route::get('/', [ProductController::class, 'index']); // Get all categories
    Route::post('/', [ProductController::class, 'store']); // Create a new category
    Route::get('/{product}', [ProductController::class, 'show']); // Get a specific category
    Route::put('/{product}', [ProductController::class, 'update']); // Update a specific category
    Route::delete('/{product}', [ProductController::class, 'destroy']); // Delete a specific category
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
    // Route::get('/{quotation}', [QuotationController::class, 'show']); // Get a specific category
    // Route::put('/{quotation}', [QuotationController::class, 'update']); // Get a specific category
    // Route::delete('/{quotation}', [QuotationController::class, 'destroy']); // Delete a specific category
    // Route::get('/files/{id}/download', [QuotationController::class, 'downloadFile']);
});

Route::group([
    'middleware' => ['api'],
    'prefix' => 'quotations'
], function () {
    Route::post('/', [QuotationController::class, 'store']); // Create a new category
   
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
