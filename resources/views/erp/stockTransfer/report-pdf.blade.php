<!DOCTYPE html>
<html>
<head>
    <title>Stock Transfer Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #2c3e50; }
        .header p { margin: 5px 0; color: #7f8c8d; }
        .summary { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background-color: #f1f3f5; font-weight: bold; }
        .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; text-transform: uppercase; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #bdc3c7; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock Transfer Report</h1>
        <p>Generated on: {{ date('d M Y, h:i A') }}</p>
    </div>

    <div class="summary">
        <div style="display: flex; justify-content: space-between;">
            <span>Total Transfers: {{ $transfers->count() }}</span>
        </div>
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
            @foreach($transfers as $transfer)
                <tr>
                    @foreach($selectedColumns as $column)
                        <td>
                            @switch($column)
                                @case('id')
                                    {{ $transfer->id }}
                                    @break
                                @case('date')
                                    {{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d-m-Y') : '-' }}
                                    @break
                                @case('product')
                                    {{ $transfer->product->name ?? '-' }}
                                    @if($transfer->variation)
                                        <br><small>({{ $transfer->variation->name }})</small>
                                    @endif
                                    @break
                                @case('source')
                                    @if($transfer->from_type == 'branch')
                                        Branch: {{ $transfer->fromBranch->name ?? '-' }}
                                    @else
                                        Warehouse: {{ $transfer->fromWarehouse->name ?? '-' }}
                                    @endif
                                    @break
                                @case('destination')
                                    @if($transfer->to_type == 'branch')
                                        Branch: {{ $transfer->toBranch->name ?? '-' }}
                                    @else
                                        Warehouse: {{ $transfer->toWarehouse->name ?? '-' }}
                                    @endif
                                    @break
                                @case('quantity')
                                    {{ $transfer->quantity }}
                                    @break
                                @case('status')
                                    {{ ucfirst($transfer->status) }}
                                    @break
                                @case('by')
                                    {{ $transfer->requestedPerson->name ?? '-' }}
                                    @break
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</body>
</html>
