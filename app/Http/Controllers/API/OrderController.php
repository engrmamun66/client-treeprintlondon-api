<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryType;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Mail\OrderEmailToAdmin; 
use App\Mail\OrderEmailToCustomer;

class OrderController extends BaseController
{
    public function index(Request $request)
    {
        try {
            // Get the 'perPage' parameter from the request, default to 10
            $perPage  = $request->per_page ?? 15;
            // Fetch paginated categories
            $orders = Order::latest()->paginate($perPage);
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
            $order = Order::with(['deliveryType','orderItems.product' => function ($query) {
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
           

            // Insert order items
            OrderItem::insert($orderItems);
            

            // Calculate total
            $total = round($subtotal + $order->tax + $order->shipping_cost, 2); // Round total to 2 decimal places

            // Update order subtotal and total
            $order->update([
                'subtotal' => round($subtotal, 2), // Round subtotal to 2 decimal places
                'total' => $total,
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
}