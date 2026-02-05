<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTemplate;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use App\Mail\OrderNotificationToOwner;
use App\Services\SmtpConfigService;
use App\Models\Balance;
use App\Models\Coupon;
use App\Models\CouponUsage;

class OrderController extends Controller
{
    public function checkoutPage()
    {
        $userId = auth()->id();
        $sessionId = session()->getId();
        $carts = Cart::with(['product', 'variation'])
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->get();
        $cartTotal = 0;

        $hasProductFreeDelivery = false;
        foreach ($carts as $cart) {
            $product = $cart->product;
            if (!$product)
                continue;
            
            $price = $this->getCartItemPrice($cart);
            $cartTotal += $price * $cart->qty;

            // Check if product has free delivery
            if ($product->free_delivery) {
                $hasProductFreeDelivery = true;
            }
        }

        // Get tax rate from general settings
        $generalSetting = \App\Models\GeneralSetting::first();
        $taxRate = $generalSetting ? ($generalSetting->tax_rate / 100) : 0.00; // Default to 0% if not set

        // Get shipping methods
        $shippingMethods = \App\Models\ShippingMethod::active()->ordered()->get();

        // Calculate initial shipping cost (0 if free delivery, otherwise first method cost)
        $initialShippingCost = $hasProductFreeDelivery ? 0 : ($shippingMethods->first() ? $shippingMethods->first()->cost : 0);

        $pageTitle = 'Checkout';
        return view('ecommerce.checkout', compact('carts', 'cartTotal', 'taxRate', 'shippingMethods', 'pageTitle', 'hasProductFreeDelivery', 'initialShippingCost'));
    }

    public function makeOrder(Request $request)
    {
        $userId = auth()->id();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'billing_address_1' => 'required|string',
            'billing_city' => 'required|string',
            'billing_city_id' => 'nullable|exists:cities,id',
            'billing_state' => 'nullable|string',
            'billing_zip_code' => 'nullable|string',
            'shipping_address_1' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_city_id' => 'nullable|exists:cities,id',
            'shipping_state' => 'nullable|string',
            'shipping_zip_code' => 'nullable|string',
            // Keep existing validation; UI may post enum or id depending on implementation
            'shipping_method' => 'required',
            'payment_method' => 'required|in:cash,online-payment',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionId = session()->getId();

        $carts = Cart::with(['product', 'variation'])
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->get();

        if ($carts->isEmpty()) {
            // For AJAX requests, return a validation-style response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Please add products before placing an order.',
                    'errors' => ['cart' => ['Your cart is empty.']]
                ], 422);
            }
            // For normal form submissions, redirect back with an error flash message
            return redirect()->back()->with('error', 'Your cart is empty. Please add products before placing an order.');
        }

        $subtotal = 0;
        $items = [];
        $invalidItemsDeleted = 0;

        foreach ($carts as $cart) {
            $product = $cart->product;
            if (!$product) {
                \Log::warning("Missing product for cart ID {$cart->id}");
                continue;
            }

            // CRITICAL: Check if cart item is missing variation_id for product with variations
            // This happens when cart was created before variation was selected
            $variationId = $cart->variation_id;

            if ($product->has_variations && !$variationId) {
                // Delete the invalid cart item - user must re-add with variation selected
                $cart->delete();
                $invalidItemsDeleted++;

                // Continue to next item instead of throwing error immediately
                // This allows processing other valid cart items
                continue;
            }

            $price = $this->getCartItemPrice($cart);
            $total = $price * $cart->qty;
            $subtotal += $total;

            // Validate that the variation_id belongs to this product
            if ($variationId) {
                $variation = \App\Models\ProductVariation::where('id', $variationId)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$variation) {
                    $errorMessage = "Invalid variation selected for product '{$product->name}'. ";
                    $errorMessage .= "Please remove this item from your cart and add it again with a valid variation.";

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                            'error_type' => 'invalid_variation',
                            'product_id' => $product->id,
                            'variation_id' => $variationId
                        ], 422);
                    }

                    throw new \Exception($errorMessage);
                }
            }

            $items[] = [
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'quantity' => $cart->qty,
                'unit_price' => $price,
                'total_price' => $total,
            ];
        }

        // Check if we have any valid items after processing
        if (empty($items)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $invalidItemsDeleted > 0
                        ? "Your cart items are missing variation selections. Please remove them from cart, go to product pages, select variations, and add them again."
                        : 'Your cart is empty. Please add products before placing an order.',
                    'errors' => ['cart' => ['Your cart is empty or contains invalid items.']]
                ], 422);
            }

            return redirect()->back()->with('error', $invalidItemsDeleted > 0
                ? "Your cart items are missing variation selections. Please remove them and add them again with variations selected."
                : 'Your cart is empty. Please add products before placing an order.');
        }

        // Get tax rate from general settings
        $generalSetting = \App\Models\GeneralSetting::first();
        $taxRate = $generalSetting ? ($generalSetting->tax_rate / 100) : 0.00; // Default to 0% if not set
        $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00; // Default to 0% if not set

        $tax = round($subtotal * $taxRate, 2);

        // Determine shipping cost (accept id or enum name)
        $shippingMethod = is_numeric($request->shipping_method)
            ? \App\Models\ShippingMethod::find($request->shipping_method)
            : \App\Models\ShippingMethod::where('name', $request->shipping_method)->first();
        $shipping = $shippingMethod ? $shippingMethod->cost : 0;

        // Check if any product in cart has free delivery enabled
        $hasProductFreeDelivery = false;
        foreach ($carts as $cart) {
            $product = $cart->product;
            if ($product && $product->free_delivery) {
                $hasProductFreeDelivery = true;
                break;
            }
        }

        // Apply free delivery if any product has free_delivery enabled
        if ($hasProductFreeDelivery) {
            $shipping = 0;
        }

        // Handle coupon validation and discount calculation
        $coupon = null;
        $couponDiscount = 0;
        $couponId = null;

        if ($request->filled('coupon_code')) {
            $couponValidation = $this->validateAndApplyCoupon($request->coupon_code, $subtotal, $carts, $userId, $sessionId);

            if (!$couponValidation['valid']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $couponValidation['message'],
                        'errors' => ['coupon_code' => [$couponValidation['message']]]
                    ], 422);
                }
                return redirect()->back()->with('error', $couponValidation['message'])->withInput();
            }

            $coupon = $couponValidation['coupon'];
            $couponDiscount = $couponValidation['discount'];
            $couponId = $coupon->id;

            // Apply free delivery if coupon has free_delivery enabled (overrides product free delivery if coupon is used)
            if ($coupon->free_delivery) {
                $shipping = 0;
            }
        }

        $total = $subtotal + $tax + $shipping - $couponDiscount;

        // Calculate COD percentage discount (for accounting only, not for invoice)
        $codDiscount = 0;
        if ($request->payment_method === 'cash' && $codPercentage > 0) {
            // Calculate COD discount: invoice total × COD percentage
            // Example: If invoice total is 1000 and COD percentage is 2%, discount = 20
            $codDiscount = round($total * $codPercentage, 2);
            // Note: COD discount is NOT subtracted from invoice total
            // It will only be applied to accounting (Balance) records
        }

        // Debug: Log COD calculation details
        if ($request->payment_method === 'cash') {
            Log::info('COD Discount Calculation', [
                'payment_method' => $request->payment_method,
                'cod_percentage_from_db' => $generalSetting->cod_percentage ?? 'not set',
                'cod_percentage_decimal' => $codPercentage,
                'invoice_total_before_cod' => $total,
                'cod_discount_calculated' => $codDiscount,
            ]);
        }

        DB::beginTransaction();
        try {
            $orderNumber = $this->generateOrderNumber();
            // For online-payment, treat as paid immediately after successful checkout; COD remains unpaid
            $isOnlinePayment = $request->payment_method === 'online-payment';
            $isInstantPaid = $isOnlinePayment;

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId ?? 0,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subtotal' => $subtotal,
                'vat' => $tax,
                'discount' => $couponDiscount,
                'delivery' => $shipping,
                'total' => $total,
                'status' => $isInstantPaid ? 'approved' : 'pending',
                'payment_method' => $request->payment_method,
                'notes' => $request->notes ?? null,
                'created_by' => $userId ?? 0,
                'coupon_id' => $couponId,
                'coupon_discount' => $couponDiscount,
            ]);

            $invTemplate = InvoiceTemplate::where('is_default', 1)->first();
            $invoiceNumber = $this->generateInvoiceNumber();

            // Resolve or create Customer (guest or logged-in)
            if ($userId) {
                $customer = Customer::firstOrCreate(
                    ['user_id' => $userId],
                    [
                        'name' => trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: 'Customer',
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'created_by' => $userId,
                        'is_active' => 1,
                    ]
                );
                $customer->name = trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: ($customer->name ?? 'Customer');
                $customer->email = $request->email ?? $customer->email;
                $customer->phone = $request->phone ?? $customer->phone;
                $customer->save();
            } else {
                // Guest: match by email or phone if possible
                $customer = Customer::firstOrCreate(
                    [
                        'email' => $request->email,
                        'phone' => $request->phone,
                    ],
                    [
                        'name' => trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: 'Customer',
                        'created_by' => 0,
                        'is_active' => 1,
                    ]
                );
            }
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'template_id' => $invTemplate ? $invTemplate->id : null,
                'operated_by' => $userId ?? 0,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'send_date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $total,
                'discount_apply' => $couponDiscount,
                'paid_amount' => $isInstantPaid ? $total : 0,
                'due_amount' => $isInstantPaid ? 0 : $total,
                'status' => $isInstantPaid ? 'paid' : 'unpaid',
                'note' => $order->notes,
                'footer_text' => null,
                'created_by' => $userId ?? 0,
                'invoice_number' => $invoiceNumber,
            ]);

            $order->invoice_id = $invoice->id;
            $order->save();

            // OPTIMIZATION: Batch load stocks upfront based on settings
            // If settings specify an ecommerce source, prioritize that. Otherwise fallback to auto-assign for WAREHOUSES.
            $ecommerceSourceType = $generalSetting->ecommerce_source_type;
            $ecommerceSourceId = $generalSetting->ecommerce_source_id;

            $variationIds = array_filter(array_column($items, 'variation_id'));
            $productIds = array_column($items, 'product_id');

            // Pre-load stocks only for the relevant source type to optimize queries
            $availableStocks = []; // [ productId => [ variationId => qty ] ] or simplified structure

            // If a specific source is configured
            if ($ecommerceSourceType && $ecommerceSourceId) {
                if ($ecommerceSourceType == 'branch') {
                     // Load Branch Stocks
                     if (!empty($variationIds)) {
                        $variationBranchStocks = \App\Models\ProductVariationStock::whereIn('variation_id', $variationIds)
                            ->where('branch_id', $ecommerceSourceId)
                            ->get()
                            ->keyBy('variation_id');
                     }
                     $productBranchStocks = \App\Models\BranchStock::whereIn('product_id', $productIds)
                        ->where('branch_id', $ecommerceSourceId)
                        ->get()
                        ->keyBy('product_id');
                } else {
                     // Load Specific Warehouse Stocks
                     if (!empty($variationIds)) {
                        $variationWarehouseStocks = \App\Models\ProductVariationStock::whereIn('variation_id', $variationIds)
                            ->where('warehouse_id', $ecommerceSourceId)
                            ->get()
                            ->keyBy('variation_id');
                     }
                     $productWarehouseStocks = \App\Models\WarehouseProductStock::whereIn('product_id', $productIds)
                        ->where('warehouse_id', $ecommerceSourceId)
                        ->get()
                        ->keyBy('product_id');
                }
            } else {
                 // Fallback: Load ALL Warehouse Stocks (Existing Behavior)
                 // Ecommerce orders ONLY use warehouse stock (no branch stock) by default
                
                // Load all variation stocks in one query
                $variationStocks = [];
                if (!empty($variationIds)) {
                    $variationWarehouseStocksData = \App\Models\ProductVariationStock::whereIn('variation_id', $variationIds)
                        ->whereNotNull('warehouse_id')
                        ->whereNull('branch_id')
                        ->where('quantity', '>', 0)
                        ->orderByDesc('quantity')
                        ->get()
                        ->groupBy('variation_id');
                    
                    foreach ($variationWarehouseStocksData as $vid => $stocks) {
                        $variationStocks[$vid] = $stocks->first();
                    }
                }
                
                // Load all product-level warehouse stocks in one query
                $productStocks = \App\Models\WarehouseProductStock::whereIn('product_id', $productIds)
                    ->where('quantity', '>', 0)
                    ->orderByDesc('quantity')
                    ->get()
                    ->groupBy('product_id')
                    ->map(function ($stocks) {
                        return $stocks->first();
                    })
                    ->all();
            }

            foreach ($items as $item) {
                // Determine stock source
                $warehouseId = null;
                $branchId = null;
                $stockAvailable = 0;

                if ($ecommerceSourceType && $ecommerceSourceId) {
                    // Strict Deduction from Configured Source
                    if ($ecommerceSourceType == 'branch') {
                        $branchId = $ecommerceSourceId;
                        // Check stock availability
                        if (!empty($item['variation_id']) && isset($variationBranchStocks[$item['variation_id']])) {
                             $stockAvailable = $variationBranchStocks[$item['variation_id']]->quantity;
                        } elseif (isset($productBranchStocks[$item['product_id']])) {
                             $stockAvailable = $productBranchStocks[$item['product_id']]->quantity;
                        }
                    } else {
                        $warehouseId = $ecommerceSourceId;
                        // Check stock availability
                        if (!empty($item['variation_id']) && isset($variationWarehouseStocks[$item['variation_id']])) {
                             $stockAvailable = $variationWarehouseStocks[$item['variation_id']]->quantity;
                        } elseif (isset($productWarehouseStocks[$item['product_id']])) {
                             $stockAvailable = $productWarehouseStocks[$item['product_id']]->quantity;
                        }
                    }
                    
                    // Validation for insufficient stock
                    if ($stockAvailable < $item['quantity']) {
                         // Throw error to be caught below
                         $productName = \App\Models\Product::find($item['product_id'])->name ?? 'Product';
                         throw new \Exception("Insufficient stock for '{$productName}' in the online store.");
                    }

                } else {
                    // Legacy/Default Behavior: Auto-assign warehouse stock
                    // For products with variations, check variation-level warehouse stock first
                    if (!empty($item['variation_id']) && isset($variationStocks[$item['variation_id']])) {
                        $variationStock = $variationStocks[$item['variation_id']];
                        if ($variationStock->quantity >= $item['quantity']) {
                            $warehouseId = $variationStock->warehouse_id;
                        } elseif ($variationStock->quantity > 0) {
                            $warehouseId = $variationStock->warehouse_id;
                        }
                    }

                    // If no variation stock found, check product-level warehouse stock
                    if (!$warehouseId && isset($productStocks[$item['product_id']])) {
                        $productStock = $productStocks[$item['product_id']];
                        if ($productStock->quantity >= $item['quantity'] || $productStock->quantity > 0) {
                            $warehouseId = $productStock->warehouse_id;
                        }
                    }
                    
                    // Note: If no warehouse found with stock, $warehouseId remains null.
                    // The subsequent deductStockForOrderItem will handle it (likely throwing error or creating negative stock depending on config, but here we expect valid deduction).
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'current_position_type' => $branchId ? 'branch' : ($warehouseId ? 'warehouse' : null), 
                    'current_position_id' => $branchId ? $branchId : ($warehouseId ? $warehouseId : null),
                ]);

                // Deduct stock from warehouse (includes validation)
                if ($warehouseId) {
                    try {
                        $this->deductStockForOrderItem($orderItem, $warehouseId);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        // ... error handling
                    }
                } elseif ($branchId) {
                     try {
                        $this->deductStockForOrderItem($orderItem, null, $branchId);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        // ... error handling
                    }
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            \App\Models\InvoiceAddress::create([
                'invoice_id' => $invoice->id,
                'billing_address_1' => $request->billing_address_1,
                'billing_address_2' => $request->billing_address_2 ?? null,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state ?? null,
                'billing_country' => $request->billing_country ?? null,
                'billing_zip_code' => $request->billing_zip_code ?? null,
                'shipping_address_1' => $request->shipping_address_1 ?? $request->billing_address_1,
                'shipping_address_2' => $request->shipping_address_2 ?? $request->billing_address_2 ?? null,
                'shipping_city' => $request->shipping_city ?? $request->billing_city,
                'shipping_state' => $request->shipping_state ?? $request->billing_state ?? null,
                'shipping_country' => $request->shipping_country ?? $request->billing_country ?? null,
                'shipping_zip_code' => $request->shipping_zip_code ?? $request->billing_zip_code ?? null,
            ]);

            // Do not create a payment record here; the gateway callback will create it for online payments,
            // and COD will be recorded when cash is actually received.

            // Clear cart for this user or guest session
            if ($userId) {
                Cart::where('user_id', $userId)->delete();
            } else {
                Cart::whereNull('user_id')->where('session_id', $sessionId)->delete();
            }

            // Apply COD percentage discount on invoice total amount for accounting (Balance) records only
            // Invoice total amount remains full (no COD discount), but accounting balance reflects COD charge
            $invoiceTotalAmount = $invoice->total_amount; // Full invoice amount (e.g., 1000)

            // Calculate accounting balance with COD discount applied
            if ($isInstantPaid) {
                // Online payment: no balance due
                $accountingBalance = 0;
            } else {
                // COD payment: apply COD percentage discount to invoice total
                // Formula: Accounting Balance = Invoice Total - (Invoice Total × COD Percentage)
                // Example: If invoice is 1000 and COD is 2%, balance = 1000 - 20 = 980
                $accountingBalance = $invoiceTotalAmount - $codDiscount;
            }

            // Log for debugging - this will help identify if COD discount is being applied
            Log::info('Balance Creation with COD Discount', [
                'order_number' => $order->order_number,
                'payment_method' => $request->payment_method,
                'cod_percentage_setting' => $generalSetting->cod_percentage ?? 0,
                'cod_percentage_decimal' => $codPercentage,
                'invoice_total_amount' => $invoiceTotalAmount,
                'cod_discount_calculated' => $codDiscount,
                'accounting_balance' => $accountingBalance,
                'is_instant_paid' => $isInstantPaid,
                'calculation_formula' => $isInstantPaid ? 'N/A (online payment)' : "{$invoiceTotalAmount} - {$codDiscount} = {$accountingBalance}",
            ]);

            Balance::create([
                'source_type' => 'customer',
                'source_id' => $customer->id, // Use customer ID instead of user_id for guest orders
                'balance' => $accountingBalance,
                'description' => 'Order Sale' . ($codDiscount > 0 ? ' (COD discount: ' . number_format($codDiscount, 2) . ')' : ''),
                'reference' => $order->order_number,
            ]);

            // Record coupon usage if coupon was applied
            if ($coupon && $couponDiscount > 0) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $userId,
                    'session_id' => $userId ? null : $sessionId,
                    'order_id' => $order->id,
                    'discount_amount' => $couponDiscount,
                    'order_total' => $subtotal,
                ]);

                // Increment coupon usage count
                $coupon->increment('used_count');
            }

            DB::commit();

            // Send emails directly (synchronously) - no queue needed
            try {
                // Configure SMTP from settings
                if (SmtpConfigService::configureFromSettings()) {
                    $customerEmail = $request->email;

                    // Send customer confirmation email (only when provided)
                    if ($customerEmail) {
                        try {
                            Mail::mailer('smtp')->to($customerEmail)->send(new OrderConfirmation($order));
                        } catch (\Exception $e) {
                            \Log::error('Failed to send customer confirmation email', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'to' => $customerEmail,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // Always notify store owner regardless of customer email
                    $ownerEmail = SmtpConfigService::getContactEmail();
                    if ($ownerEmail && filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
                        try {
                            Mail::mailer('smtp')->to($ownerEmail)->send(new OrderNotificationToOwner($order));
                        } catch (\Exception $e) {
                            \Log::error('Failed to send owner notification email', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'to' => $ownerEmail,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    \Log::warning('SMTP not configured - emails not sent', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the order
                \Log::error('Failed to send order emails', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }

            // Check if this is an AJAX request for online payment
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $total,
                    'message' => 'Order created successfully'
                ]);
            }

            // Ensure the order number is safe for URLs (avoid '#' fragment issues)
            $encodedOrderNumber = urlencode($order->order_number);

            return redirect()->route('order.success', $encodedOrderNumber);

            // return redirect(url('/order-success/' . $encodedOrderNumber));



        } catch (\Exception $e) {
            \Log::alert($e);
            DB::rollBack();

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancelOrder($id)
    {
        $order = Order::with('items')->find($id);

        if (!$order) {
            $tab = request()->get('tab', 'profile');
            return redirect()->route('profile.edit', ['tab' => $tab])->with('error', 'Order not found.');
        }

        // Only allow cancellation of pending or approved orders
        if (!in_array($order->status, ['pending', 'approved'])) {
            $tab = request()->get('tab', 'profile');
            return redirect()->route('profile.edit', ['tab' => $tab])->with('error', 'Order cannot be cancelled at this stage.');
        }

        DB::beginTransaction();
        try {
            // Restore stock for each order item
            foreach ($order->items as $item) {
                $this->restoreStockForOrderItem($item);
            }

            $order->status = 'cancelled';
            $order->save();

            DB::commit();

            $tab = request()->get('tab', 'profile');
            return redirect()->route('profile.edit', ['tab' => $tab])->with('success', 'Order cancelled successfully. Stock has been restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            $tab = request()->get('tab', 'profile');
            return redirect()->route('profile.edit', ['tab' => $tab])->with('error', 'Failed to cancel order. Please try again.');
        }
    }

    public function deleteOrder($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::with(['items', 'invoice', 'invoice.payments'])->findOrFail($id);

            // Check if user owns this order
            if ($order->user_id !== auth()->id()) {
                return redirect()->back()->with('error', 'You are not authorized to delete this order.');
            }

            // Only allow deletion of cancelled or pending orders
            if (!in_array($order->status, ['cancelled', 'pending'])) {
                return redirect()->back()->with('error', 'Only cancelled or pending orders can be deleted.');
            }

            // Delete related invoice payments
            if ($order->invoice && $order->invoice->payments) {
                $order->invoice->payments()->delete();
            }

            // Delete invoice items
            if ($order->invoice) {
                $order->invoice->items()->delete();
                $order->invoice->delete();
            }

            // Delete order items
            $order->items()->delete();

            // Delete the order
            $order->delete();

            DB::commit();

            // Check if tab parameter exists in request
            $tab = request()->get('tab', 'profile');
            return redirect()->route('profile.edit', ['tab' => $tab])->with('success', 'Order deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order deletion failed: ' . $e->getMessage());
            $tab = request()->get('tab', 'profile');
            return redirect()->route('profile.edit', ['tab' => $tab])->with('error', 'Failed to delete order. Please try again.');
        }
    }

    public function show($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->first();
        $pageTitle = $order->order_number;
        return view('ecommerce.orderdetails', compact('order', 'pageTitle'));
    }

    /**
     * Validate and apply coupon
     */
    private function validateAndApplyCoupon($couponCode, $subtotal, $carts, $userId = null, $sessionId = null)
    {
        $coupon = Coupon::where('code', strtoupper(trim($couponCode)))->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid coupon code.'
            ];
        }

        // Check if coupon is valid
        if (!$coupon->isValid()) {
            return [
                'valid' => false,
                'message' => 'This coupon is not currently valid or has expired.'
            ];
        }

        // Check if user can use this coupon
        if (!$coupon->canBeUsedBy($userId, $sessionId)) {
            return [
                'valid' => false,
                'message' => 'You have reached the usage limit for this coupon.'
            ];
        }

        // Check minimum purchase requirement
        if (!$coupon->meetsMinimumPurchase($subtotal)) {
            return [
                'valid' => false,
                'message' => 'Minimum purchase amount of ' . number_format($coupon->min_purchase, 2) . '৳ required for this coupon.'
            ];
        }

        // Check if coupon applies to cart items
        $applicableSubtotal = 0;
        foreach ($carts as $cart) {
            $product = $cart->product;
            if (!$product)
                continue;

            if ($coupon->appliesToProduct($product->id, $product->category_id)) {
                $price = $this->getCartItemPrice($cart);
                $applicableSubtotal += $price * $cart->qty;
            }
        }

        if ($applicableSubtotal == 0) {
            return [
                'valid' => false,
                'message' => 'This coupon does not apply to any items in your cart.'
            ];
        }

        // Calculate discount on applicable subtotal
        $discount = $coupon->calculateDiscount($applicableSubtotal);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'free_delivery' => $coupon->free_delivery ?? false,
            'message' => 'Coupon applied successfully!'
        ];
    }

    /**
     * Validate coupon API endpoint
     */
    public function validateCoupon(Request $request)
    {
        $couponCode = $request->input('coupon_code');
        $userId = auth()->id();
        $sessionId = session()->getId();

        if (!$couponCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Please enter a coupon code.'
            ]);
        }

        // Get cart items
        $carts = Cart::with(['product', 'variation'])
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'valid' => false,
                'message' => 'Your cart is empty.'
            ]);
        }

        // Calculate subtotal
        $subtotal = 0;
        foreach ($carts as $cart) {
            $product = $cart->product;
            if (!$product)
                continue;
            $price = $this->getCartItemPrice($cart);
            $subtotal += $price * $cart->qty;
        }

        $validation = $this->validateAndApplyCoupon($couponCode, $subtotal, $carts, $userId, $sessionId);

        if ($validation['valid']) {
            // Get tax and shipping for total calculation
            $generalSetting = \App\Models\GeneralSetting::first();
            $taxRate = $generalSetting ? ($generalSetting->tax_rate / 100) : 0.00;
            $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00;
            $tax = round($subtotal * $taxRate, 2);

            // Get shipping cost (if available in request)
            $shippingCost = $request->input('shipping_cost', 0);

            // Check if any product in cart has free delivery enabled
            $hasProductFreeDelivery = false;
            foreach ($carts as $cart) {
                $product = $cart->product;
                if ($product && $product->free_delivery) {
                    $hasProductFreeDelivery = true;
                    break;
                }
            }

            // Apply free delivery if any product has free_delivery enabled or coupon has free_delivery
            if ($hasProductFreeDelivery || ($validation['free_delivery'] ?? false)) {
                $shippingCost = 0;
            }

            $total = $subtotal + $tax + $shippingCost - $validation['discount'];

            // Calculate COD percentage discount (for display only, not subtracted from invoice total)
            $codDiscount = 0;
            $paymentMethod = $request->input('payment_method', 'cash'); // Default to cash if not provided
            if ($paymentMethod === 'cash' && $codPercentage > 0) {
                $codDiscount = round($total * $codPercentage, 2);
                // Note: COD discount is NOT subtracted from invoice total
                // It will only be applied to accounting (Balance) records
            }

            return response()->json([
                'valid' => true,
                'message' => $validation['message'],
                'discount' => $validation['discount'],
                'formatted_discount' => number_format($validation['discount'], 2),
                'free_delivery' => $hasProductFreeDelivery || ($validation['free_delivery'] ?? false),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shippingCost,
                'cod_discount' => $codDiscount,
                'formatted_cod_discount' => $codDiscount > 0 ? number_format($codDiscount, 2) : '0.00',
                'total' => $total,
                'formatted_subtotal' => number_format($subtotal, 2),
                'formatted_tax' => number_format($tax, 2),
                'formatted_shipping' => number_format($shippingCost, 2),
                'formatted_total' => number_format($total, 2),
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => $validation['message']
        ]);
    }

    private function generateInvoiceNumber()
    {
        $generalSettings = \App\Models\GeneralSetting::first();
        $prefix = $generalSettings ? $generalSettings->invoice_prefix : 'INV';

        $lastInvoice = Invoice::latest('id')->first();
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;

        // Format: INV-000001
        return $prefix . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    public function orderSuccess(string $orderId)
    {
        $pageTitle = $orderId;

        // Get order data for GTM purchase event
        $order = Order::where('order_number', $orderId)
            ->with(['items.product.category', 'invoice.invoiceAddress'])
            ->first();

        return view('ecommerce.order-success', compact('orderId', 'pageTitle', 'order'));
    }

    private function generateOrderNumber()
    {
        $lastOrder = Order::latest('id')->first();
        $nextId = $lastOrder ? $lastOrder->id + 1 : 1;

        // Format: ORD-000001
        return 'ORD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Search cities API endpoint
     */
    public function searchCities(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['cities' => []]);
        }

        $cities = \App\Models\City::active()
            ->search($query)
            ->ordered()
            ->limit(50)
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'display_name' => $city->display_name,
                    'country' => $city->country,
                    'state' => $city->state,
                ];
            });

        return response()->json(['cities' => $cities]);
    }

    /**
     * Get shipping methods for a city
     */
    public function getShippingMethodsForCity(Request $request)
    {
        $cityId = $request->get('city_id');

        if (!$cityId) {
            // Return all active shipping methods if no city selected
            $methods = \App\Models\ShippingMethod::active()->ordered()->get();
        } else {
            // Get methods available for this city
            $methods = \App\Models\ShippingMethod::active()
                ->forCity($cityId)
                ->ordered()
                ->get();
        }

        $cartTotal = 0;
        $userId = auth()->id();
        $sessionId = session()->getId();
        $carts = Cart::with(['product', 'variation'])
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->get();

        foreach ($carts as $cart) {
            $product = $cart->product;
            if (!$product)
                continue;
                
            $price = $this->getCartItemPrice($cart);
            $cartTotal += $price * $cart->qty;
        }

        $generalSetting = \App\Models\GeneralSetting::first();
        $taxRate = $generalSetting ? ($generalSetting->tax_rate / 100) : 0.00;

        $shippingMethods = $methods->map(function ($method) use ($cityId) {
            $cost = $cityId ? $method->getCostForCity($cityId) : $method->cost;
            return [
                'id' => $method->id,
                'name' => $method->name,
                'description' => $method->description,
                'delivery_time' => $method->delivery_time,
                'cost' => (float) $cost,
                'formatted_cost' => number_format($cost, 2),
            ];
        });

        // Check if any product in cart has free delivery enabled
        $hasProductFreeDelivery = false;
        foreach ($carts as $cart) {
            $product = $cart->product;
            if ($product && $product->free_delivery) {
                $hasProductFreeDelivery = true;
                break;
            }
        }

        $tax = round($cartTotal * $taxRate, 2);
        $selectedShipping = $shippingMethods->first();
        $shippingCost = $selectedShipping ? $selectedShipping['cost'] : 0;

        // Apply free delivery if any product has free_delivery enabled
        if ($hasProductFreeDelivery) {
            $shippingCost = 0;
        }

        $total = $cartTotal + $tax + $shippingCost;

        return response()->json([
            'shipping_methods' => $shippingMethods,
            'has_product_free_delivery' => $hasProductFreeDelivery,
            'summary' => [
                'subtotal' => $cartTotal,
                'tax' => $tax,
                'shipping' => $shippingCost,
                'total' => $total,
                'formatted_subtotal' => number_format($cartTotal, 2),
                'formatted_tax' => number_format($tax, 2),
                'formatted_shipping' => number_format($shippingCost, 2),
                'formatted_total' => number_format($total, 2),
            ]
        ]);
    }

    /**
     * Download PDF invoice for an order
     */
    public function downloadInvoice($orderNumber)
    {
        $order = Order::with(['items.product', 'items.variation', 'invoice.invoiceAddress'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        try {
            $pdf = \App\Services\InvoicePdfService::generate($order);

            if (!$pdf) {
                abort(500, 'Failed to generate PDF invoice');
            }

            $filename = \App\Services\InvoicePdfService::getFilename($order);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Failed to download order invoice PDF', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            abort(500, 'Failed to generate PDF invoice');
        }
    }

    /**
     * Deduct stock for an order item
     */
    /**
     * Deduct stock for an order item
     */
    private function deductStockForOrderItem($orderItem, $warehouseId = null, $branchId = null)
    {
        $productId = $orderItem->product_id;
        $variationId = $orderItem->variation_id;
        $quantity = $orderItem->quantity;
        $userId = auth()->id() ?? 0;

        if ($branchId) {
             // DEDUCT FROM BRANCH
             if ($variationId) {
                // Variation Branch Stock
                $variationStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->first();

                if ($variationStock) {
                    if ($variationStock->quantity >= $quantity) {
                        $variationStock->quantity -= $quantity;
                        $variationStock->updated_by = $userId;
                        $variationStock->last_updated_at = now();
                        $variationStock->save();
                    } else {
                         throw new \Exception("Insufficient stock at the selected branch.");
                    }
                } else {
                     throw new \Exception("No stock record found for this variation at the selected branch.");
                }
             } else {
                // Product Branch Stock
                $productStock = \App\Models\BranchStock::where('product_id', $productId)
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->first();
                
                 if ($productStock) {
                    if ($productStock->quantity >= $quantity) {
                        $productStock->quantity -= $quantity;
                        $productStock->updated_by = $userId;
                        $productStock->last_updated_at = now();
                        $productStock->save();
                    } else {
                         throw new \Exception("Insufficient stock at the selected branch.");
                    }
                } else {
                     throw new \Exception("No stock record found for this product at the selected branch.");
                }
             }

        } elseif ($warehouseId) {
            // DEDUCT FROM WAREHOUSE (Existing Logic)
            if ($variationId) {
                $variationStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                    ->where('warehouse_id', $warehouseId)
                    ->whereNull('branch_id')
                    ->lockForUpdate()
                    ->first();

                if ($variationStock) {
                    if ($variationStock->quantity >= $quantity) {
                        $variationStock->quantity -= $quantity;
                        $variationStock->updated_by = $userId;
                        $variationStock->last_updated_at = now();
                        $variationStock->save();

                        Log::info('Stock deducted from variation warehouse', [
                            'variation_id' => $variationId,
                            'warehouse_id' => $warehouseId,
                            'quantity_deducted' => $quantity,
                            'remaining_quantity' => $variationStock->quantity,
                            'order_item_id' => $orderItem->id
                        ]);
                    } else {
                        throw new \Exception("Insufficient stock for variation ID {$variationId} at warehouse ID {$warehouseId}. Required: {$quantity}, Available: {$variationStock->quantity}");
                    }
                } else {
                    throw new \Exception("No stock record found for variation ID {$variationId} at warehouse ID {$warehouseId}");
                }
            } else {
                // For products without variations, deduct from product-level stock
                $productStock = \App\Models\WarehouseProductStock::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                if ($productStock) {
                    if ($productStock->quantity >= $quantity) {
                        $productStock->quantity -= $quantity;
                        $productStock->updated_by = $userId;
                        $productStock->last_updated_at = now();
                        $productStock->save();

                        Log::info('Stock deducted from product warehouse', [
                            'product_id' => $productId,
                            'warehouse_id' => $warehouseId,
                            'quantity_deducted' => $quantity,
                            'remaining_quantity' => $productStock->quantity,
                            'order_item_id' => $orderItem->id
                        ]);
                    } else {
                        throw new \Exception("Insufficient stock for product ID {$productId} at warehouse ID {$warehouseId}. Required: {$quantity}, Available: {$productStock->quantity}");
                    }
                } else {
                    throw new \Exception("No stock record found for product ID {$productId} at warehouse ID {$warehouseId}");
                }
            }
        }
    }

    /**
     * Restore stock for an order item
     */
    private function restoreStockForOrderItem($item)
    {
        $productId = $item->product_id;
        $variationId = $item->variation_id;
        $quantity = $item->quantity;
        $warehouseId = $item->current_position_id;
        $userId = auth()->id() ?? 0;

        // If no warehouse ID is set, we can't restore to a specific location
        // In this case, we'll restore to the first available warehouse or create a default one
        if (!$warehouseId) {
            Log::warning('Order item has no warehouse ID, restoring to default warehouse', [
                'order_item_id' => $item->id,
                'product_id' => $productId,
                'variation_id' => $variationId
            ]);

            // Try to find any warehouse with stock for this product/variation
            if ($variationId) {
                $existingStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                    ->whereNotNull('warehouse_id')
                    ->whereNull('branch_id')
                    ->first();
                $warehouseId = $existingStock ? $existingStock->warehouse_id : 1; // Default to warehouse 1
            } else {
                $existingStock = \App\Models\WarehouseProductStock::where('product_id', $productId)
                    ->first();
                $warehouseId = $existingStock ? $existingStock->warehouse_id : 1; // Default to warehouse 1
            }
        }

        // For products with variations, restore to variation-level stock
        if ($variationId) {
            $variationStock = \App\Models\ProductVariationStock::firstOrCreate(
                [
                    'variation_id' => $variationId,
                    'warehouse_id' => $warehouseId,
                    'branch_id' => null
                ],
                [
                    'quantity' => 0,
                    'updated_by' => $userId,
                    'last_updated_at' => now()
                ]
            );

            $variationStock->quantity += $quantity;
            $variationStock->updated_by = $userId;
            $variationStock->last_updated_at = now();
            $variationStock->save();

            Log::info('Stock restored to variation warehouse', [
                'variation_id' => $variationId,
                'warehouse_id' => $warehouseId,
                'quantity_restored' => $quantity,
                'new_quantity' => $variationStock->quantity,
                'order_item_id' => $item->id
            ]);
        } else {
            // For products without variations, restore to product-level stock
            $productStock = \App\Models\WarehouseProductStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId
                ],
                [
                    'quantity' => 0,
                    'updated_by' => $userId,
                    'last_updated_at' => now()
                ]
            );

            $productStock->quantity += $quantity;
            $productStock->updated_by = $userId;
            $productStock->last_updated_at = now();
            $productStock->save();

            Log::info('Stock restored to product warehouse', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity_restored' => $quantity,
                'new_quantity' => $productStock->quantity,
                'order_item_id' => $item->id
            ]);
        }
    }

    /**
     * Get the consistent price for a cart item
     */
    private function getCartItemPrice($cart)
    {
        $product = $cart->product;
        if (!$product) return 0;

        if ($cart->variation) {
            return (float) $cart->variation->effective_price;
        }

        return (float) $product->effective_price;
    }
}
