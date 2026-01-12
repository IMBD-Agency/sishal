<!DOCTYPE html>
<html>
<head>
    <title>Customer List</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8f9fa; padding: 8px; border: 1px solid #ddd; text-align: left; font-size: 10px; }
        td { padding: 8px; border: 1px solid #ddd; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; color: white; display: inline-block; }
        .bg-success { background-color: #198754; }
        .bg-danger { background-color: #dc3545; }
        .bg-warning { background-color: #ffc107; color: #000; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customer List</h1>
        <p>Generated on {{ date('M d, Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Info</th>
                <th>Location</th>
                <th class="text-center">Status</th>
                <th class="text-center">Joined Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>#{{ $customer->id }}</td>
                    <td>
                        <strong>{{ $customer->name }}</strong><br>
                        @if($customer->is_premium)
                            <span style="color: #ffc107; font-size: 9px;">★ Premium User</span>
                        @endif
                    </td>
                    <td>
                        {{ $customer->phone }}<br>
                        <span style="color: #666;">{{ $customer->email }}</span>
                    </td>
                    <td>
                        {{ $customer->address_1 }}<br>
                        {{ $customer->city }}{{ $customer->city ? ',' : '' }} {{ $customer->country }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $customer->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-center">{{ $customer->created_at->format('d M, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} ERP System - Customer Management
    </div>
</body>
</html>
