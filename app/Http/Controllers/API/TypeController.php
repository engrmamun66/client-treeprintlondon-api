<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Type;

class TypeController extends BaseController
{

    public function index()
    {
        try {
            // Fetch paginated brands
            $types = Type::orderBy('id', 'DESC')->get();
            // Return paginated response
            return $this->sendResponse($types, 'Type list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    public function getTypewiseCategoryList()
    {
        try {
            // Find the category by ID
            $types = Type::with('categories.children')->get();
    
            // Check if the category exists
            if (!$types ) {
                return $this->sendError('Types not found.', [], 404);
            }
    
            // Return the category data
            return $this->sendResponse($types , 'Types found.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

}
