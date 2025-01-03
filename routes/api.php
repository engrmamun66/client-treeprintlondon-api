<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\BrandController;
 
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