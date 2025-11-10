<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Order Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .message {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="message">
        <h2>New Order Received</h2>
        <p>A new order has been placed on your website.</p>
        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('F j, Y') }}</p>
        <p><strong>Customer:</strong> {{ $order->name ?? 'N/A' }}</p>
        <p><strong>Total Amount:</strong> {{ number_format($order->total, 2) }}৳</p>
        <p style="margin-top: 20px;">Please find the detailed invoice attached as a PDF.</p>
    </div>
</body>
</html>
