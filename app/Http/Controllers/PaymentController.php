<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Initialize SSL Commerce payment
     */
    public function initializePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $order = Order::with(['invoice', 'customer'])->findOrFail($request->order_id);
            
            // Generate unique transaction ID
            $tranId = config('ssl_commerce.tran_id_prefix') . time() . rand(1000, 9999);
            
            // For local development without SSL, we'll use a different approach
            // SSL Commerce requires HTTPS URLs, so we'll use a mock payment for local testing
            if (config('app.env') === 'local' && !config('app.ssl_enabled', false)) {
                return $this->handleLocalPayment($order, $request->amount, $tranId);
            }
            
            // Prepare payment data for SSL Commerce
            $paymentData = [
                'store_id' => config('ssl_commerce.store_id'),
                'store_passwd' => config('ssl_commerce.store_password'),
                'total_amount' => $request->amount,
                'currency' => config('ssl_commerce.currency'),
                'tran_id' => $tranId,
                'success_url' => config('app.url') . config('ssl_commerce.success_url'),
                'fail_url' => config('app.url') . config('ssl_commerce.fail_url'),
                'cancel_url' => config('app.url') . config('ssl_commerce.cancel_url'),
                'ipn_url' => config('app.url') . config('ssl_commerce.ipn_url'),
                'cus_name' => $order->name ?? ($order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Customer'),
                'cus_email' => $order->email ?? ($order->customer ? $order->customer->email : 'customer@example.com'),
                'cus_add1' => $order->billing_address_1 ?? 'N/A',
                'cus_add2' => $order->billing_address_2 ?? '',
                'cus_city' => $order->billing_city ?? 'Dhaka',
                'cus_state' => $order->billing_state ?? 'Dhaka',
                'cus_postcode' => $order->billing_zip_code ?? '1000',
                'cus_country' => $order->billing_country ?? 'Bangladesh',
                'cus_phone' => $order->phone ?? ($order->customer ? $order->customer->phone : 'N/A'),
                'cus_fax' => '',
                'ship_name' => $order->name ?? ($order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Customer'),
                'ship_add1' => $order->shipping_address_1 ?? $order->billing_address_1 ?? 'N/A',
                'ship_add2' => $order->shipping_address_2 ?? $order->billing_address_2 ?? '',
                'ship_city' => $order->shipping_city ?? $order->billing_city ?? 'Dhaka',
                'ship_state' => $order->shipping_state ?? $order->billing_state ?? 'Dhaka',
                'ship_postcode' => $order->shipping_zip_code ?? $order->billing_zip_code ?? '1000',
                'ship_country' => $order->shipping_country ?? $order->billing_country ?? 'Bangladesh',
                'shipping_method' => 'Courier', // SSL Commerce requires: YES, NO, Courier, Air, Ship, or Truck
                'product_name' => $this->getProductNames($order), // Get product names from order items
                'product_category' => 'Electronic', // SSL Commerce requires: Electronic, topup, bus ticket, air ticket, etc.
                'product_profile' => 'physical-goods', // SSL Commerce requires: general, physical-goods, non-physical-goods, airline-tickets, travel-vertical, telecom-vertical
                'value_a' => $order->id, // Order ID
                'value_b' => $order->invoice_id ?? '', // Invoice ID
                'value_c' => 'order_payment', // Payment type
                'value_d' => $order->order_number, // Order number
            ];

            // Create payment record
            $payment = Payment::create([
                'payment_for' => 'order',
                'pos_id' => $order->id,
                'invoice_id' => $order->invoice_id,
                'payment_date' => now()->toDateString(),
                'amount' => $request->amount,
                'payment_method' => 'ssl_commerce',
                'reference' => $tranId,
                'note' => 'SSL Commerce payment initiated',
                'status' => 'pending',
            ]);

            // Update order with transaction ID
            $order->update([
                'payment_reference' => $tranId,
                'payment_status' => 'pending'
            ]);

            // Make request to SSL Commerce API
            $sslResponse = $this->makeSslCommerceRequest($paymentData);
            
            if ($sslResponse['status'] === 'SUCCESS') {
                Log::info('SSL Commerce payment initialized successfully', [
                    'order_id' => $order->id,
                    'tran_id' => $tranId,
                    'amount' => $request->amount,
                    'session_key' => $sslResponse['sessionkey']
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $sslResponse['GatewayPageURL'],
                    'tran_id' => $tranId,
                    'session_key' => $sslResponse['sessionkey']
                ]);
            } else {
                Log::error('SSL Commerce payment failed', [
                    'order_id' => $order->id,
                    'tran_id' => $tranId,
                    'error' => $sslResponse['failedreason'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $sslResponse['failedreason'] ?? 'Payment initialization failed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('SSL Commerce payment initialization failed', [
                'error' => $e->getMessage(),
                'order_id' => $request->order_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle local payment for development without SSL
     */
    private function handleLocalPayment($order, $amount, $tranId)
    {
        // Create payment record
        $payment = Payment::create([
            'payment_for' => 'order',
            'pos_id' => $order->id,
            'invoice_id' => $order->invoice_id,
            'payment_date' => now()->toDateString(),
            'amount' => $amount,
            'payment_method' => 'ssl_commerce',
            'reference' => $tranId,
            'note' => 'Local development payment (SSL Commerce mock)',
            'status' => 'completed',
        ]);

        // Update order
        $order->update([
            'payment_reference' => $tranId,
            'payment_status' => 'paid',
            'status' => 'approved'
        ]);

        // Update invoice
        if ($order->invoice) {
            $invoice = $order->invoice;
            $invoice->update([
                'paid_amount' => $invoice->paid_amount + $amount,
                'due_amount' => max(0, $invoice->total_amount - ($invoice->paid_amount + $amount)),
                'status' => ($invoice->paid_amount + $amount) >= $invoice->total_amount ? 'paid' : 'partial'
            ]);
        }

        Log::info('Local payment completed', [
            'order_id' => $order->id,
            'tran_id' => $tranId,
            'amount' => $amount
        ]);

        return response()->json([
            'success' => true,
            'payment_url' => route('order.success', $order->order_number),
            'tran_id' => $tranId,
            'local_development' => true
        ]);
    }

    /**
     * Make request to SSL Commerce API
     */
    private function makeSslCommerceRequest($paymentData)
    {
        $apiUrl = config('ssl_commerce.api_url');
        
        // Use cURL to make the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($paymentData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, config('ssl_commerce.verify_ssl', true));
        curl_setopt($ch, CURLOPT_TIMEOUT, config('ssl_commerce.timeout', 30));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Log::error('SSL Commerce cURL error', ['error' => $error]);
            return ['status' => 'FAILED', 'failedreason' => 'Connection error: ' . $error];
        }
        
        if ($httpCode !== 200) {
            Log::error('SSL Commerce HTTP error', ['http_code' => $httpCode, 'response' => $response]);
            return ['status' => 'FAILED', 'failedreason' => 'HTTP error: ' . $httpCode];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('SSL Commerce JSON decode error', ['response' => $response]);
            return ['status' => 'FAILED', 'failedreason' => 'Invalid response format'];
        }
        
        return $decodedResponse;
    }

    /**
     * Get product names from order items
     */
    private function getProductNames($order)
    {
        $productNames = [];
        
        // Get order items
        $orderItems = $order->orderItems ?? collect();
        
        foreach ($orderItems as $item) {
            if ($item->product && $item->product->name) {
                $productNames[] = $item->product->name;
            }
        }
        
        // If no order items found, try to get from cart (for new orders)
        if (empty($productNames)) {
            $carts = \App\Models\Cart::where('user_id', $order->user_id)->get();
            foreach ($carts as $cart) {
                if ($cart->product && $cart->product->name) {
                    $productNames[] = $cart->product->name;
                }
            }
        }
        
        // If still no products, use a default name
        if (empty($productNames)) {
            $productNames[] = 'Product';
        }
        
        // Return comma-separated product names (max 100 characters as per SSL Commerce)
        $productNamesString = implode(', ', $productNames);
        return strlen($productNamesString) > 100 ? substr($productNamesString, 0, 97) . '...' : $productNamesString;
    }

    /**
     * Build SSL Commerce payment URL
     */
    private function buildPaymentUrl($data)
    {
        // For SSL Commerce, we need to use a form submission approach
        // instead of a long URL to avoid "Request-URI Too Long" errors
        $url = config('ssl_commerce.api_url');
        
        // Create a temporary form data storage
        $formId = 'ssl_form_' . $data['tran_id'];
        
        // Store the form data in session temporarily
        session([$formId => $data]);
        
        // Return a URL that will handle the form submission
        return route('payment.ssl-form', ['form_id' => $formId]);
    }

    /**
     * Show SSL Commerce form
     */
    public function showSslForm(Request $request, $formId)
    {
        $formData = session($formId);
        
        $pageTitle = 'Redirecting to Payment Gateway';
        
        if (!$formData) {
            // If no session data, try to get it from the request or return error
            return view('ecommerce.payment.ssl-form', [
                'formData' => null,
                'error' => 'Payment session expired. Please try again.',
                'pageTitle' => $pageTitle
            ]);
        }
        
        // Clear the session data
        session()->forget($formId);
        
        return view('ecommerce.payment.ssl-form', compact('formData', 'pageTitle'));
    }

    /**
     * Handle successful payment
     */
    public function paymentSuccess(Request $request)
    {
        try {
            $tranId = $request->input('tran_id');
            $payment = Payment::where('reference', $tranId)->first();

            if (!$payment) {
                return redirect()->route('payment.failed')->with('error', 'Payment not found.');
            }

            // Verify payment with SSL Commerce
            $verificationResult = $this->verifyPayment($tranId);

            if ($verificationResult['success']) {
                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'note' => 'Payment completed successfully via SSL Commerce'
                ]);

                // Update order status
                $order = Order::find($payment->pos_id);
                if ($order) {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'approved'
                    ]);

                    // Update invoice
                    if ($order->invoice) {
                        $invoice = $order->invoice;
                        $invoice->update([
                            'paid_amount' => $invoice->paid_amount + $payment->amount,
                            'due_amount' => max(0, $invoice->total_amount - ($invoice->paid_amount + $payment->amount)),
                            'status' => ($invoice->paid_amount + $payment->amount) >= $invoice->total_amount ? 'paid' : 'partial'
                        ]);
                    }
                }

                Log::info('Payment successful', [
                    'tran_id' => $tranId,
                    'order_id' => $order->id ?? null
                ]);

                return redirect()->route('order.success', $order->order_number ?? '')
                    ->with('success', 'Payment completed successfully!');
            } else {
                return redirect()->route('payment.failed')
                    ->with('error', 'Payment verification failed.');
            }

        } catch (\Exception $e) {
            Log::error('Payment success handling failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()->route('payment.failed')
                ->with('error', 'Payment processing failed.');
        }
    }

    /**
     * Handle failed payment
     */
    public function paymentFailed(Request $request)
    {
        $tranId = $request->input('tran_id');
        
        if ($tranId) {
            $payment = Payment::where('reference', $tranId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'note' => 'Payment failed via SSL Commerce'
                ]);

                $order = Order::find($payment->pos_id);
                if ($order) {
                    $order->update([
                        'payment_status' => 'failed'
                    ]);
                }
            }
        }

        Log::info('Payment failed', [
            'tran_id' => $tranId,
            'request' => $request->all()
        ]);

        $pageTitle = 'Payment Failed';
        return view('ecommerce.payment.failed', compact('tranId', 'pageTitle'));
    }

    /**
     * Handle cancelled payment
     */
    public function paymentCancelled(Request $request)
    {
        $tranId = $request->input('tran_id');
        
        if ($tranId) {
            $payment = Payment::where('reference', $tranId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'cancelled',
                    'note' => 'Payment cancelled by user'
                ]);
            }
        }

        Log::info('Payment cancelled', [
            'tran_id' => $tranId,
            'request' => $request->all()
        ]);

        $pageTitle = 'Payment Cancelled';
        return view('ecommerce.payment.cancelled', compact('tranId', 'pageTitle'));
    }

    /**
     * Handle IPN (Instant Payment Notification)
     */
    public function handleIpn(Request $request)
    {
        try {
            $tranId = $request->input('tran_id');
            $status = $request->input('status');
            $amount = $request->input('amount');
            $currency = $request->input('currency');

            Log::info('IPN received', $request->all());

            $payment = Payment::where('reference', $tranId)->first();
            
            if (!$payment) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Verify IPN data
            $verificationResult = $this->verifyPayment($tranId);

            if ($verificationResult['success'] && $status === 'VALID') {
                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'note' => 'Payment completed via IPN'
                ]);

                // Update order and invoice
                $order = Order::find($payment->pos_id);
                if ($order) {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'approved'
                    ]);

                    if ($order->invoice) {
                        $invoice = $order->invoice;
                        $invoice->update([
                            'paid_amount' => $invoice->paid_amount + $payment->amount,
                            'due_amount' => max(0, $invoice->total_amount - ($invoice->paid_amount + $payment->amount)),
                            'status' => ($invoice->paid_amount + $payment->amount) >= $invoice->total_amount ? 'paid' : 'partial'
                        ]);
                    }
                }

                return response()->json(['status' => 'success']);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'note' => 'Payment failed via IPN'
                ]);

                return response()->json(['status' => 'failed']);
            }

        } catch (\Exception $e) {
            Log::error('IPN handling failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'IPN processing failed'], 500);
        }
    }

    /**
     * Verify payment with SSL Commerce
     */
    private function verifyPayment($tranId)
    {
        try {
            $data = [
                'store_id' => config('ssl_commerce.store_id'),
                'store_passwd' => config('ssl_commerce.store_password'),
                'tran_id' => $tranId,
                'format' => 'json'
            ];

            $url = config('ssl_commerce.validation_url') . '?' . http_build_query($data);
            
            $response = $this->makeHttpRequest($url);
            
            if ($response && isset($response['status'])) {
                return [
                    'success' => $response['status'] === 'VALID',
                    'data' => $response
                ];
            }

            return ['success' => false, 'data' => null];

        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'error' => $e->getMessage(),
                'tran_id' => $tranId
            ]);

            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Make HTTP request
     */
    private function makeHttpRequest($url, $data = null, $method = 'GET')
    {
        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => config('ssl_commerce.timeout'),
                CURLOPT_SSL_VERIFYPEER => config('ssl_commerce.verify_ssl'),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]
            ]);

            if ($method === 'POST' && $data) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);

            if ($error) {
                throw new \Exception('cURL Error: ' . $error);
            }

            if ($httpCode !== 200) {
                throw new \Exception('HTTP Error: ' . $httpCode);
            }

            return json_decode($response, true);

        } catch (\Exception $e) {
            Log::error('HTTP request failed', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);

            return null;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($tranId)
    {
        $payment = Payment::where('reference', $tranId)->first();
        
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json([
            'status' => $payment->status,
            'amount' => $payment->amount,
            'created_at' => $payment->created_at,
            'order_id' => $payment->pos_id
        ]);
    }
}
