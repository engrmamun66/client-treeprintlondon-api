<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Color;
use App\Http\Requests\ColorRequest;
use Illuminate\Http\Request;

class ColorController extends BaseController
{

    public function index(Request $request)
    {
        try {
            // Get the 'perPage' parameter from the request, default to 15
            $perPage = $request->per_page ?? 15;

            // Fetch paginated brands
            $colors = Color::orderBy('id', 'DESC')->paginate($perPage);

            // Return paginated response
            return $this->sendResponse($colors, 'Color list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            // Find the brand by ID
            $color = Color::find($id);

            // Check if the brand exists
            if (!$color) {
                return $this->sendError('Color not found.', [], 404);
            }

            // Return the brand data
            return $this->sendResponse($color, 'Color found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function store(ColorRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Set default status if not explicitly provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = true; // Default to active
            }

            $color = Color::create($validatedData);

            return $this->sendResponse($color, 'Color created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function update(ColorRequest $request, Color $color)
    {
        try {
            $validatedData = $request->validated();

            // Update the brand with validated data
            $color->update($validatedData);

            return $this->sendResponse($color, 'Color updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Color $color)
    {
        try {
            // Delete the brand
            $color->delete();
            return $this->sendResponse(null, 'Color deleted successfully.', 200);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
