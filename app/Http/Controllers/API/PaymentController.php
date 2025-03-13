<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use App\Models\Order;
use App\Models\Payment;
use Log;
class PaymentController extends BaseController
{
    // public function createPaymentIntent(Request $request)
    // {
    //     Stripe::setApiKey(env('STRIPE_SECRET'));

    //     $paymentIntent = PaymentIntent::create([
    //         'amount' => $request->amount * 100, // Amount in cents
    //         'currency' => 'usd',
    //         'metadata' => [
    //             'order_number' => $request->order_number, // Replace with your order ID
    //         ],
    //     ]);

    //     return response()->json([
    //         'clientSecret' => $paymentIntent->client_secret,
    //     ]);
    // }
    // public function handleWebhook(Request $request)
    // {
    //     $payload = $request->getContent();
    //     $sigHeader = $request->header('Stripe-Signature');
    //     $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

    //     try {
    //         $event = Webhook::constructEvent(
    //             $payload, $sigHeader, $endpointSecret
    //         );
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Invalid signature'], 400);
    //     }

    //     // Handle the event
    //     switch ($event->type) {
    //         case 'payment_intent.succeeded':
    //             $paymentIntent = $event->data->object;
    //             $orderId = $paymentIntent->metadata->order_id; // Access order_id from metadata
    //             $this->logPayment($orderId, $paymentIntent->id, 'succeeded');
    //             $this->updateOrder($paymentIntent, 'succeeded');
    //             break;
    //         case 'payment_intent.payment_failed':
    //             $paymentIntent = $event->data->object;
    //             $orderId = $paymentIntent->metadata->order_id; // Access order_id from metadata
    //             $this->logPayment($orderId, $paymentIntent->id, 'failed', $paymentIntent->last_payment_error->message);
    //             $this->updateOrder($paymentIntent, 'failed');
    //             break;
    //     }

    //     return response()->json(['status' => 'success']);
    // }

    // private function logPayment($orderId, $paymentIntentId, $status, $error = null)
    // {
    //     PaymentLog::create([
    //         'order_id' => $orderId,
    //         'payment_intent_id' => $paymentIntentId,
    //         'status' => $status,
    //         'error' => $error,
    //     ]);
    // }
    // private function updateOrder($paymentIntent, $status)
    // {
    //     // Retrieve the order ID from metadata
    //     $orderId = $paymentIntent->metadata->order_id;

    //     // Find the order
    //     $order = Order::find($orderId);

    //     if ($order) {
    //         // Update the order with payment details
    //         $paidAmount = $paymentIntent->amount / 100;
    //         $order->update([
    //             'payment_method' => $paymentIntent->payment_method_types[0] ?? 'card', // Default to 'card'
    //             'payment_status' => $status,
    //             'paid_amount' => $paidAmount,
    //         ]);
    //     } else {
    //         Log::error('Order not found:', ['order_id' => $orderId]);
    //     }
    // }

    public function createPayment(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();

        $order = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $request->amount,
                    ],
                ],
            ],
            'application_context' => [
                'return_url' => env('PAYPAL_PAYMENT_SUCCESS_URL').'?order_id='.$request->order_id,
                'cancel_url' => env('PAYPAL_PAYMENT_CANCEL_URL'),
            ],
        ];

        $response = $provider->createOrder($order);

        return response()->json($response);
    }

    public function paymentSuccess(Request $request)
    {
        try{
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
        
            // Capture the payment using the token
            $response = $provider->capturePaymentOrder($request->token);
        
            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                // Extract payment method
                $paymentMethod = 'Unknown'; // Default value
                if (isset($response['payment_source'])) {
                    // Check for PayPal payment
                    if (isset($response['payment_source']['paypal'])) {
                        $paymentMethod = 'PayPal';
                    }
                    // Check for credit card payment
                    elseif (isset($response['payment_source']['card'])) {
                        $paymentMethod = 'Credit Card';
                    }
                }
                $amount = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
                $currency = $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
                // Save payment information to the database
                $payment = Payment::create([
                    'payment_id' => $response['id'], // PayPal transaction ID
                    'payer_id' => $response['payer']['payer_id'], // PayPal payer ID
                    'order_id' => $request->order_id, // Your custom order ID
                    'amount' =>  $amount, // Payment amount
                    'currency' => $currency, // Currency
                    'status' => $response['status'], // Payment status
                    'payment_method' => $paymentMethod, // Payment method
                ]);
        
                return response()->json([
                    'message' => 'Payment successful and saved',
                    'payment' => $payment,
                ]);
            }
    
            return response()->json(['message' => 'Payment failed'], 400);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function paymentCancel()
    {
        return response()->json(['message' => 'Payment cancelled']);
    }
}
