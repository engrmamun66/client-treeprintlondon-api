<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use App\Models\Order;
use App\Models\PaymentLog;
use Log;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount * 100, // Amount in cents
            'currency' => 'usd',
            'metadata' => [
                'order_number' => $request->order_number, // Replace with your order ID
            ],
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $orderId = $paymentIntent->metadata->order_id; // Access order_id from metadata
                $this->logPayment($orderId, $paymentIntent->id, 'succeeded');
                $this->updateOrder($paymentIntent, 'succeeded');
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $orderId = $paymentIntent->metadata->order_id; // Access order_id from metadata
                $this->logPayment($orderId, $paymentIntent->id, 'failed', $paymentIntent->last_payment_error->message);
                $this->updateOrder($paymentIntent, 'failed');
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function logPayment($orderId, $paymentIntentId, $status, $error = null)
    {
        PaymentLog::create([
            'order_id' => $orderId,
            'payment_intent_id' => $paymentIntentId,
            'status' => $status,
            'error' => $error,
        ]);
    }
    private function updateOrder($paymentIntent, $status)
    {
        // Retrieve the order ID from metadata
        $orderId = $paymentIntent->metadata->order_id;

        // Find the order
        $order = Order::find($orderId);

        if ($order) {
            // Update the order with payment details
            $paidAmount = $paymentIntent->amount / 100;
            $order->update([
                'payment_method' => $paymentIntent->payment_method_types[0] ?? 'card', // Default to 'card'
                'payment_status' => $status,
                'paid_amount' => $paidAmount,
            ]);
        } else {
            Log::error('Order not found:', ['order_id' => $orderId]);
        }
    }
}
