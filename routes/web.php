<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/run-artisan', function () {
    \Artisan::call('optimize:clear');
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
      \Artisan::call('storage:link');
    return "Artisan commands executed!";
});
Route::get('/storage/product/{type}/{filename}', function ($type, $filename) {
    $path = storage_path('app/public/product/' . $type . '/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return Response::make($file, 200)
                   ->header('Content-Type', $type);
});
Route::get('/storage/quotation/{filename}', function ($filename) {
    $path = storage_path('app/public/quotation/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return Response::make($file, 200)
                   ->header('Content-Type', $type);
});