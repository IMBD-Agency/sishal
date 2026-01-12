<!DOCTYPE html>
<html>
<head>
    <title>Purchase Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #2c3e50; font-size: 20px; }
        .header p { margin: 2px 0; color: #7f8c8d; }
        .summary { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; }
        th { background-color: #f1f3f5; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .status-badge { padding: 2px 5px; border-radius: 3px; font-size: 9px; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #bdc3c7; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Report</h1>
        <p>Generated on: {{ date('d M Y, h:i A') }}</p>
    </div>

    <div class="summary">
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 0;"><strong>Total Assignments:</strong> {{ $purchases->count() }}</td>
                <td style="border: none; padding: 0; text-align: right;"><strong>Total Amount:</strong> ৳{{ number_format($purchases->sum(fn($p) => $p->items->sum('total_price')), 2) }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
                <tr>
                    @foreach($selectedColumns as $column)
                        <td class="{{ in_array($column, ['total']) ? 'text-end' : '' }}">
                            @switch($column)
                                @case('id')
                                    #{{ $purchase->id }}
                                    @break
                                @case('date')
                                    {{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') : '-' }}
                                    @break
                                @case('location')
                                    @if($purchase->ship_location_type == 'branch')
                                        Branch: {{ $purchase->location_id }} {{-- Ideally name, but name might require extra query or eager load --}}
                                        @php
                                            $loc = \App\Models\Branch::find($purchase->location_id);
                                            echo $loc->name ?? 'Unknown';
                                        @endphp
                                    @else
                                        Warehouse: 
                                        @php
                                            $loc = \App\Models\Warehouse::find($purchase->location_id);
                                            echo $loc->name ?? 'Unknown';
                                        @endphp
                                    @endif
                                    @break
                                @case('status')
                                    {{ ucfirst($purchase->status) }}
                                    @break
                                @case('total')
                                    {{ number_format($purchase->items->sum('total_price'), 2) }}
                                    @break
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Printed by: {{ auth()->user()->name ?? 'System' }} | &copy; {{ date('Y') }} {{ config('app.name') }}
    </div>
</body>
</html>
