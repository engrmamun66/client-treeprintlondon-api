<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\OrderCoupon;
use App\Models\DeliveryType;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Mail\OrderEmailToAdmin;
use App\Mail\OrderEmailToCustomer;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    // public function index(Request $request)
    // {
    //     try {
    //         // Get the 'perPage' parameter from the request, default to 10
    //         $perPage  = $request->per_page ?? 15;
    //         // Fetch paginated categories
    //         $orders = Order::latest()->paginate($perPage);
    //         // Return paginated response
    //         return $this->sendResponse($orders, 'Order list retrieved successfully.');

    //     } catch (Exception $e) {
    //         return $this->sendError($e, ["Line- ".$e->getLine().' '.$e->getMessage()], 500);
    //     }
    // }
    
    
    public function index(Request $request)
    {
        try {
            // Get the 'perPage' parameter from the request, default to 15
            $perPage = $request->per_page ?? 15;
            
            // Initialize the query
            $query = Order::query();
            
            // Apply filters
            if ($request->has('is_recent') && $request->is_recent) {
                $query->whereDate('created_at', '>=', now()->subDays(7));
            }
            
            if ($request->has('order_status_id')) {
                $query->where('order_status_id', $request->order_status_id);
            }
            
            if ($request->has('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhere('customer_first_name', 'like', "%{$search}%")
                      ->orWhere('customer_last_name', 'like', "%{$search}%");
                });
            }
            
            // Order by latest and paginate
            $orders = $query->latest()->paginate($perPage);
            
            // Return paginated response
            return $this->sendResponse($orders, 'Order list retrieved successfully.');
    
        } catch (Exception $e) {
            return $this->sendError($e, ["Line- ".$e->getLine().' '.$e->getMessage()], 500);
        }
    }
    
    
    
    public function show($order_number)
    {
        try {
            // Fetch the order with order items and product details
            $order = Order::with(['deliveryType', 'orderCoupon.coupon', 'orderItems.product' => function ($query) {
                $query->with(['brand', 'category', 'images', 'colors', 'sizes', 'genders']);
            }])->where('order_number', $order_number)->first();
    
            if (!$order) {
                return $this->sendError('Order not found.', [], 404);
            }
    
            // Return the order with product details
            return $this->sendResponse($order, 'Order found.');
    
        } catch (Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
      /**
     * Create a new order.
     */
    public function store(CreateOrderRequest $request)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Generate a unique order number
            $orderNumber = 'ORD' . time();

            // Fetch delivery type if provided
            $deliveryType = null;
            if ($request->delivery_type_id) {
                $deliveryType = DeliveryType::find($request->delivery_type_id);
            }
            
            
            // Initialize coupon variables
            $coupon = null;
            $discountAmount = 0;
            $originalTotal = 0;
            

            // Validate and apply coupon if provided
            if ($request->coupon_code) {
                 
                $coupon = Coupon::where('code', $request->coupon_code)->first();
        
                if (!$coupon || !$coupon->isValidForEmail($request->customer_email)) {
                    return $this->sendError('Invalid or expired coupon code', [], 400);
                }
            }
            
          
              

            // Create the order with initial values
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_first_name' => $request->customer_first_name,
                'customer_last_name' => $request->customer_last_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'country' => $request->country,
                'city' => $request->city,
                'state' => $request->state,
                'zipcode' => $request->zipcode,
                'billing_address' => $request->billing_address,
                'delivery_type_id' => $request->delivery_type_id,
                'subtotal' => 0, // Will be calculated below
                'tax' => round($request->tax ?? 0, 2), // Round tax to 2 decimal places
                'shipping_cost' => round($deliveryType->cost ?? 0, 2), // Round shipping cost to 2 decimal places
                'total' => 0, // Will be calculated below
                'payment_status' => $request->payment_status ?? 'pending', // Default to 'pending'
                'order_status_id' => $request->order_status_id ?? 1, // Default to 'pending' status ID
                'notes' => $request->notes,
            ]);
            
             

            // Calculate subtotal and total
            $subtotal = 0;
            $orderItems = [];
         

            // Create order items
            foreach ($request->items as $item) {
                // Calculate total price for the item
                $unitPrice = $item['discounted_unit_price'] ?? $item['unit_price'];
                $totalPrice = round($unitPrice * $item['quantity'], 2); // Round total price to 2 decimal places
                $subtotal += $totalPrice;

                // Prepare order item data
                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_size_id' => $item['product_size_id'],
                    'product_color_id' => $item['product_color_id'] ?? null, // Optional field
                    'unit_price' => round($item['unit_price'], 2), // Round unit price to 2 decimal places
                    'discounted_unit_price' => isset($item['discounted_unit_price']) ? round($item['discounted_unit_price'], 2) : null, // Round discounted price to 2 decimal places
                    'quantity' => $item['quantity'],
                    'total_price' => $totalPrice,
                    'discount' => isset($item['discount']) ? round($item['discount'], 2) : null, // Round discount to 2 decimal places
                    'note' => isset($item['note']) ? $item['note'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
           

            // Insert order items
            OrderItem::insert($orderItems);
           
            

             // Calculate totals
            $originalTotal = round($subtotal + $order->tax + $order->shipping_cost, 2);

             
            
            // Apply coupon discount if valid
            if ($coupon) {
                $discountAmount = round($subtotal * ($coupon->discount_value / 100), 2);

                $discountAmount = min($discountAmount, $subtotal);
                
                // Record coupon usage
                OrderCoupon::create([
                    'coupon_id' => $coupon->id,
                    'order_id' => $order->id,
                    'customer_email' => $order->customer_email,
                    'used_at' => now()
                ]);

            }
           

            // Update order with final amounts
            $order->update([
                'subtotal' => round($subtotal, 2),
                'original_total' => $originalTotal,
                'discount_amount' => $discountAmount,
                'total' => round($originalTotal - $discountAmount, 2),
            ]);
             
           
            // Commit the transaction
            DB::commit();
              
            Mail::to('support@teeprintlondon.co.uk')->send(new OrderEmailToAdmin($order));
            Mail::to($order->customer_email)->send(new OrderEmailToCustomer($order));

            // Return success response
            return $this->sendResponse($order, 'Order created successfully.');

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    
    
    public function updateOrderStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|numeric',
                'order_status_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError("Validation Error", $validator->errors(), 422);
            }
            // Fetch the order with order items and product details
            $order = Order::where('id', $request->order_id)->first();

            if (!$order) {
                return $this->sendError('Order not found.', [], 404);
            }

            $order->order_status_id = $request->order_status_id;
            $order->save();
            // Return the order with product details
            return $this->sendResponse($order, 'Order status updated successfully.');
    
        } catch (Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
    
    
     public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Find the order with its items
            $order = Order::with('orderItems')->find($id);

            if (!$order) {
                return $this->sendError('Order not found.', [], 404);
            }

            // Delete all associated order items first
            OrderItem::where('order_id', $order->id)->delete();

            // Then delete the order
            $order->delete();

            DB::commit();

            return $this->sendResponse([], 'Order deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}