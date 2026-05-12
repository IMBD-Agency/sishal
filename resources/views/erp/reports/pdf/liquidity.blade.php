<!DOCTYPE html>
<html>
<head>
    <title>Liquidity Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .summary-box { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Financial Liquidity Report</h2>
        <p>Sisal Fashion</p>
        <p>Period: {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}</p>
    </div>

    <div class="summary-box">
        <table style="width: 100%;">
            <tr>
                <td><strong>Total Cash:</strong> ৳{{ number_format($cashBalance, 2) }}</td>
                <td><strong>Total Bank:</strong> ৳{{ number_format($bankBalance, 2) }}</td>
                <td><strong>Total Wallet:</strong> ৳{{ number_format($walletBalance, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" style="padding-top: 10px; font-size: 16px;"><strong>Combined Liquidity: ৳{{ number_format($totalLiquidity, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Account Name</th>
                <th class="text-center">Opening</th>
                <th class="text-center">Inflow (+)</th>
                <th class="text-center">Outflow (-)</th>
                <th class="text-end">Closing (৳)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Main Cash</td>
                <td class="text-center">{{ number_format($openingCash, 2) }}</td>
                <td class="text-center">{{ number_format($cashIn, 2) }}</td>
                <td class="text-center">{{ number_format($cashOut, 2) }}</td>
                <td class="text-end">{{ number_format($cashBalance, 2) }}</td>
            </tr>
            <tr>
                <td>Bank Account</td>
                <td class="text-center">{{ number_format($openingBank, 2) }}</td>
                <td class="text-center">{{ number_format($bankIn, 2) }}</td>
                <td class="text-center">{{ number_format($bankOut, 2) }}</td>
                <td class="text-end">{{ number_format($bankBalance, 2) }}</td>
            </tr>
            <tr>
                <td>Mobile Wallets</td>
                <td class="text-center">{{ number_format($openingWallet, 2) }}</td>
                <td class="text-center">{{ number_format($walletIn, 2) }}</td>
                <td class="text-center">{{ number_format($walletOut, 2) }}</td>
                <td class="text-end">{{ number_format($walletBalance, 2) }}</td>
            </tr>
            <tr class="fw-bold">
                <td>GRAND TOTAL</td>
                <td class="text-center">{{ number_format($openingCash + $openingBank + $openingWallet, 2) }}</td>
                <td class="text-center">{{ number_format($cashIn + $bankIn + $walletIn, 2) }}</td>
                <td class="text-center">{{ number_format($cashOut + $bankOut + $walletOut, 2) }}</td>
                <td class="text-end">{{ number_format($totalLiquidity, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
