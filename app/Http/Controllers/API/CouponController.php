<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\OrderCoupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class CouponController extends BaseController
{
    public function index(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 15);
            $coupons = Coupon::latest()->paginate($perPage);

            return $this->sendResponse($coupons, 'Coupon retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    // Create new coupon
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:20|unique:coupons',
                'discount_value' => 'required|numeric|min:0.01',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean'
            ]);
            $coupon = Coupon::create($validated);
            return $this->sendResponse($coupon, 'Coupon created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    // Get single coupon
    public function show(Coupon $coupon)
    {
        return $this->sendResponse($coupon, 'Coupon retireved successfully.');
    }

    // Update coupon
    public function update(Request $request, Coupon $coupon)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'code' => ['sometimes', 'string', 'max:20', Rule::unique('coupons')->ignore($coupon->id)],
                'discount_value' => 'sometimes|numeric|min:0.01',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date',
                'is_active' => 'sometimes|boolean'
            ]);

            $coupon->update($validated);
            return $this->sendResponse($coupon, 'Coupon updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    // Delete coupon
    public function destroy($id)
    {
        // Prevent deletion if coupon is already in use
        $orderCoupons = OrderCoupon::where('coupon_id', $id)->count();
        if ($orderCoupons > 0) {
            return $this->sendResponse([], 'Cannot delete coupon that has been used');
        }

        Coupon::where('id', $id)->delete();

        return $this->sendResponse([], 'Coupon deleted successfully.');
    }

    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'customer_email' => 'nullable|email'
        ]);
        
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }
        
        $coupon = Coupon::where('code', $request->coupon_code)->first();
        
        if (!$coupon || !$coupon->isValidForEmail($request->customer_email)) {
            return $this->sendError('Invalid or expired coupon code', [], 400);
        }
        
        $discount = $coupon->calculateDiscount($request->subtotal);
        
        return $this->sendResponse([
            'discount' => round($discount, 2),
            'coupon' =>  $coupon
        ], 'Coupon applied successfully');
    }

    
}