<!DOCTYPE html>
<html>
<head>
    <title>Supplier Database Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2d5a4c; padding-bottom: 10px; }
        .header h1 { color: #2d5a4c; margin: 0; font-size: 22px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #2d5a4c; color: white; padding: 10px 5px; text-transform: uppercase; font-size: 10px; text-align: left; }
        td { padding: 8px 5px; border-bottom: 1px solid #eee; vertical-align: top; }
        .info-label { font-weight: bold; color: #666; font-size: 8px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supplier Database Report</h1>
        <p>Generated on {{ date('F d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Supplier Info</th>
                <th style="width: 20%;">Contact Details</th>
                <th style="width: 20%;">Closing Balance</th>
                <th style="width: 30%;">Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $index => $supplier)
                @php $balance = $supplier->balance; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $supplier->name }}</div>
                        <div class="info-label">SUP-{{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}</div>
                        @if($supplier->company_name)
                            <div style="font-size: 9px; margin-top: 2px;">{{ $supplier->company_name }}</div>
                        @endif
                    </td>
                    <td>
                        <div><span class="info-label">P:</span> {{ $supplier->phone }}</div>
                        @if($supplier->email)
                            <div style="font-size: 9px;">{{ $supplier->email }}</div>
                        @endif
                    </td>
                    <td style="font-weight: bold;">
                        <span style="color: {{ $balance > 0 ? '#dc3545' : ($balance < 0 ? '#198754' : '#6c757d') }}">
                            {{ number_format(abs($balance), 2) }} ৳
                        </span>
                        <div style="font-size: 8px; color: #666;">
                            {{ $balance > 0 ? 'DUE' : ($balance < 0 ? 'ADVANCE' : 'CLEAR') }}
                        </div>
                    </td>
                    <td>
                        @if($supplier->address)
                            <div style="font-size: 9px;">{{ $supplier->address }}</div>
                        @endif
                        <div style="font-weight: bold; font-size: 9px;">
                            {{ $supplier->city }} {{ $supplier->zip_code ? ', ' . $supplier->zip_code : '' }}
                            @if($supplier->country)
                                - {{ strtoupper($supplier->country) }}
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} IMBD Agency - ERP Management System | Page {PAGE_NUM}
    </div>
</body>
</html>
