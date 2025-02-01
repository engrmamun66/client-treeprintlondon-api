<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Gender;

class GenderController extends BaseController
{

    public function index()
    {
        try {
            // Fetch paginated brands
            $genders = Gender::orderBy('id', 'DESC')->get();

            // Return paginated response
            return $this->sendResponse($genders, 'Gender list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
