<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
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

            // Create the order
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
                'tax' => $request->tax ?? 0, // Use provided tax or default to 0
                'shipping_cost' => $request->shipping_cost ?? 0, // Use provided shipping cost or default to 0
                'total' => 0, // Will be calculated below
                'payment_status' => $request->payment_status ?? 'pending', // Default to 'pending'
                'order_status_id' => $request->order_status_id ?? 1, // Default to 'pending' status ID (assuming 1 is pending)
                'notes' => $request->notes,
            ]);

            // Calculate subtotal and total
            $subtotal = 0;
            $orderItems = [];

            // Create order items
            foreach ($request->items as $item) {
                $totalPrice = ($item['discounted_unit_price'] ?? $item['unit_price']) * $item['quantity'];
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_size_id' => $item['product_size_id'],
                    'product_color_id' => $item['product_color_id'] ?? null, // Optional field
                    'unit_price' => $item['unit_price'],
                    'discounted_unit_price' => $item['discounted_unit_price'] ?? null, // Optional field
                    'quantity' => $item['quantity'],
                    'total_price' => $totalPrice,
                    'discount' => $item['discount'] ?? null, // Optional field
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert order items
            OrderItem::insert($orderItems);

            // Update order subtotal and total
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $order->tax + $order->shipping_cost,
            ]);

            // Commit the transaction
            DB::commit();

            // Return success response
            return $this->sendResponse($order, 'Order created successfully.');

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}