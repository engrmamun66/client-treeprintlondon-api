<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Size;
use App\Http\Requests\SizeRequest;
use Illuminate\Http\Request;

class SizeController extends BaseController
{

    public function index()
    {
        try {
            // Fetch paginated brands
            $sizes = Size::orderBy('id', 'DESC')->get();

            // Return paginated response
            return $this->sendResponse($sizes, 'Size list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            // Find the brand by ID
            $size = Size::find($id);

            // Check if the brand exists
            if (!$size) {
                return $this->sendError('Size not found.', [], 404);
            }

            // Return the brand data
            return $this->sendResponse($size, 'Size found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function store(SizeRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Set default status if not explicitly provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = true; // Default to active
            }

            $size = Size::create($validatedData);

            return $this->sendResponse($size, 'Size created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function update(SizeRequest $request, Size $size)
    {
        try {
            $validatedData = $request->validated();

            // Update the brand with validated data
            $size->update($validatedData);

            return $this->sendResponse($size, 'Size updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Size $size)
    {
        try {
            // Delete the brand
            $size->delete();
            return $this->sendResponse(null, 'Size deleted successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
