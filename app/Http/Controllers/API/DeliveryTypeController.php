<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\DeliveryType;

class DeliveryTypeController extends BaseController
{

    public function index()
    {
        try {
            // Fetch paginated brands
            $deliveryTypes = DeliveryType::orderBy('id', 'DESC')->get();

            // Return paginated response
            return $this->sendResponse($deliveryTypes, 'Delivery Type list retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}
