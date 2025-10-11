@extends('ecommerce.master')

@section('main-section')
<style>
    .order-details-page {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .order-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
        position: relative;
    }
    
    .order-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #00512C, #10B981, #3B82F6);
    }
    
    .order-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-bottom: 1px solid #e2e8f0;
        padding: 1.5rem;
    }
    
    .order-number {
        color: #00512C;
        font-weight: 800;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }
    
    .order-date {
        color: #64748b;
        font-size: 0.9rem;
    }
    
    .order-total {
        color: #00512C;
        font-weight: 700;
        font-size: 1.25rem;
    }
    
    .section-title {
        color: #00512C;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .section-title i {
        color: #00512C;
        font-size: 1rem;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }
    
    .info-value {
        color: #64748b;
        font-size: 0.9rem;
        text-align: right;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .status-shipping {
        background: #e0e7ff;
        color: #3730a3;
    }
    
    .status-delivered {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .table-custom {
        border: none;
    }
    
    .table-custom thead th {
        background: #f8fafc;
        border: none;
        color: #374151;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 1rem 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .table-custom tbody td {
        border: none;
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .table-custom tbody tr:last-child td {
        border-bottom: none;
    }
    
    .product-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .product-image {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .product-name {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }
    
    .sku-badge {
        background: #f1f5f9;
        color: #64748b;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .quantity-badge {
        background: #00512C;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .price-text {
        color: #374151;
        font-weight: 600;
    }
    
    .total-text {
        color: #00512C;
        font-weight: 700;
        font-size: 1rem;
    }
    
    .payment-status-paid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .payment-status-partial {
        background: #fef3c7;
        color: #92400e;
    }
    
    .payment-status-unpaid {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .payment-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .payment-item:last-child {
        margin-bottom: 0;
    }
    
    .print-btn {
        background: #00512C;
        border: 1px solid #00512C;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .print-btn:hover {
        background: #004124;
        border-color: #004124;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 81, 44, 0.3);
    }
    
    .back-btn {
        background: transparent;
        border: 1px solid #e2e8f0;
        color: #64748b;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .back-btn:hover {
        background: #f8fafc;
        border-color: #00512C;
        color: #00512C;
    }
</style>

<div class="order-details-page">
    <div class="container py-4">
        <!-- Back Button -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('profile.edit') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Profile
                </a>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
            <!-- Order Header Card -->
            <div class="order-card">
                <div class="order-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="order-number">Order #{{ $order->order_number }}</h1>
                            <div class="d-flex align-items-center gap-3">
                                <span class="status-badge 
                                    {{ 
                                        $order->status == 'pending' ? 'status-pending' : 
                                        ($order->status == 'approved' ? 'status-approved' : 
                                        ($order->status == 'shipping' ? 'status-shipping' : 
                                        ($order->status == 'delivered' ? 'status-delivered' : 
                                        ($order->status == 'cancelled' ? 'status-cancelled' : 'status-pending')))) 
                                    }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <div class="order-date">Placed on {{ $order->created_at ? $order->created_at->format('M d, Y') : '-' }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="order-total">Total: {{ number_format($order->total, 2) }}৳</div>
                            <div class="mt-2">
                                <a href="#" class="print-btn" onclick="window.print()">
                                    <i class="fas fa-print"></i>
                                    Print Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="section-title">
                                <i class="fas fa-user"></i>
                                Customer Information
                            </h5>
                            <div class="info-item">
                                <span class="info-label">Name:</span>
                                <span class="info-value">{{ $order->name ?? ($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? '') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value">{{ $order->email ?? $order->user->email ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone:</span>
                                <span class="info-value">{{ $order->phone ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="section-title">
                                <i class="fas fa-truck"></i>
                                Delivery Information
                            </h5>
                            <div class="info-item">
                                <span class="info-label">Address:</span>
                                <span class="info-value">{{ optional($order->invoice->invoiceAddress)->billing_address_1 ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">City:</span>
                                <span class="info-value">{{ optional($order->invoice->invoiceAddress)->billing_city ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">State:</span>
                                <span class="info-value">{{ optional($order->invoice->invoiceAddress)->billing_state ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ZIP:</span>
                                <span class="info-value">{{ optional($order->invoice->invoiceAddress)->billing_zip_code ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Country:</span>
                                <span class="info-value">{{ optional($order->invoice->invoiceAddress)->billing_country ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items Card -->
            <div class="order-card">
                <div class="card-body p-4">
                    <h5 class="section-title">
                        <i class="fas fa-shopping-bag"></i>
                        Order Items
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            @if($item->product && $item->product->image)
                                                <img src="{{ asset($item->product->image) }}" alt="Product" class="product-image">
                                            @else
                                                <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                            <span class="product-name">{{ $item->product->name ?? 'Product Deleted' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="sku-badge">{{ $item->product->sku ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="quantity-badge">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-end price-text">{{ number_format($item->unit_price, 2) }}৳</td>
                                    <td class="text-end total-text">{{ number_format($item->total_price, 2) }}৳</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Information Row -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="order-card">
                        <div class="card-body p-4">
                            <h5 class="section-title">
                                <i class="fas fa-file-invoice-dollar"></i>
                                Invoice & Payment
                            </h5>
                            <div class="info-item">
                                <span class="info-label">Invoice Number:</span>
                                <span class="info-value">{{ $order->invoice->invoice_number ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status:</span>
                                <span class="info-value">
                                    <span class="status-badge 
                                        {{ 
                                            $order->invoice->status == 'paid' ? 'payment-status-paid' : 
                                            ($order->invoice->status == 'partial' ? 'payment-status-partial' : 'payment-status-unpaid') 
                                        }}">
                                        {{ ucfirst($order->invoice->status ?? '-') }}
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Paid Amount:</span>
                                <span class="info-value price-text">{{ number_format($order->invoice->paid_amount ?? 0, 2) }}৳</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Due Amount:</span>
                                <span class="info-value price-text">{{ number_format($order->invoice->due_amount ?? 0, 2) }}৳</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Method:</span>
                                <span class="info-value">{{ ucfirst($order->payment_method ?? '-') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Note:</span>
                                <span class="info-value">{{ $order->notes ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="order-card">
                        <div class="card-body p-4">
                            <h5 class="section-title">
                                <i class="fas fa-history"></i>
                                Payment History
                            </h5>
                            @if($order->invoice && $order->invoice->payments->count())
                                @foreach($order->invoice->payments as $payment)
                                <div class="payment-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold price-text">{{ number_format($payment->amount, 2) }}৳</div>
                                            <small class="text-muted">{{ $payment->payment_date }}</small>
                                        </div>
                                        <span class="status-badge" style="background: #00512C; color: white;">{{ ucfirst($payment->payment_method) }}</span>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-receipt text-muted" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                    <div class="text-muted">No payments recorded.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection