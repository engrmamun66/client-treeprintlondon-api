<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;


trait FileUpload
{
    /**
     * Upload a file to the specified directory.
     *
     * @param  \Illuminate\Http\UploadedFile $file
     * @param  string $path
     * @param  array|null $resizeDimensions [width, height] for resizing
     * @return string Path of the uploaded file
     */
    public function FileUpload($file, $path = 'undefined', $resizeDimensions = null)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $path . '/' . $filename;

        if ($resizeDimensions) {
            // Resize the image and store in public disk
            $resizedImage = Image::read($file)
                ->resize($resizeDimensions[0], $resizeDimensions[1], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

            // Save the resized image in the public directory
            Storage::disk('public')->put($filePath, (string) $resizedImage->encode());
        } else {
            // Store the original file in public disk
            $filePath = $file->storeAs($path, $filename, 'public');
        }

        return $filePath;
    }
}