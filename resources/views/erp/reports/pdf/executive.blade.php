<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; }
        .period { color: #555; font-size: 14px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        td { border: 1px solid #dee2e6; padding: 10px; }
        .section-header { background-color: #eee; font-weight: bold; font-size: 13px; }
        .total-row { background-color: #f1f3f5; font-weight: bold; }
        .profit-row { background-color: #28a745; color: white; font-weight: bold; font-size: 16px; }
        .loss-row { background-color: #dc3545; color: white; font-weight: bold; font-size: 16px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 5px; color: #000; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 10px; }
        .bg-success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">EXECUTIVE PERFORMANCE REPORT</div>
        <div class="period">Period: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</div>
        <div style="color: #777;">Generated on: {{ date('d M Y, h:i A') }}</div>
    </div>

    <h3>SECTION 1: REVENUE BREAKDOWN</h3>
    <table>
        <thead>
            <tr>
                <th>Income Channel</th>
                <th class="text-center">Volume</th>
                <th class="text-right">Amount (৳)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Retail Sales (POS)</td>
                <td class="text-center">{{ $posSales->count }}</td>
                <td class="text-right">{{ number_format($posSales->net_sales ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Online Orders (Ecommerce)</td>
                <td class="text-center">{{ $onlineSales->count }}</td>
                <td class="text-right">{{ number_format($onlineSales->net_sales ?? 0, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL GROSS REVENUE</td>
                <td class="text-center">{{ $posSales->count + $onlineSales->count }}</td>
                <td class="text-right">{{ number_format($grossRevenue, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>SECTION 2: COST OF GOODS SOLD (COGS)</h3>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Unit Cost Applied</th>
                <th class="text-right">Total Cost (৳)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Retail Goods Cost</td>
                <td class="text-right">Weighted Avg</td>
                <td class="text-right">-{{ number_format($posCost, 2) }}</td>
            </tr>
            <tr>
                <td>Online Goods Cost</td>
                <td class="text-right">Weighted Avg</td>
                <td class="text-right">-{{ number_format($onlineCost, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">TOTAL COGS</td>
                <td class="text-right">-{{ number_format($totalCogs, 2) }}</td>
            </tr>
            <tr class="total-row" style="background-color: #e9ecef;">
                <td colspan="2">GROSS PROFIT (Revenue - COGS)</td>
                <td class="text-right">{{ number_format($grossProfit, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>SECTION 3: OPERATING EXPENSES</h3>
    <table>
        <thead>
            <tr>
                <th>Expense Category (Ledger)</th>
                <th class="text-right">Amount (৳)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($operatingExpenses as $expense)
            <tr>
                <td>{{ $expense->name }}</td>
                <td class="text-right">-{{ number_format($expense->total, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL OPERATING EXPENSES</td>
                <td class="text-right">-{{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; padding: 20px; text-align: center;" class="{{ $netProfit >= 0 ? 'profit-row' : 'loss-row' }}">
        FINAL NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }} FOR THE PERIOD: ৳{{ number_format($netProfit, 2) }}
    </div>

    <div style="page-break-before: always;"></div>

    <h3>SECTION 4: INVENTORY VALUE APPRAISAL (SNAPSHOT)</h3>
    <p style="color: #777; margin-bottom: 10px;">The following values represent the total wealth currently held in stock, evaluated at three different price points.</p>
    <table>
        <thead>
            <tr>
                <th>Valuation Type</th>
                <th>Price Point Name</th>
                <th class="text-right">Total Asset Value (৳)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Investment Value</td>
                <td><strong>Purchase Price (Cost)</strong></td>
                <td class="text-right">{{ number_format($stockValue->total_cost, 2) }}</td>
            </tr>
            <tr>
                <td>B2B Value</td>
                <td><strong>Wholesale Price</strong></td>
                <td class="text-right">{{ number_format($stockValue->total_wholesale, 2) }}</td>
            </tr>
            <tr>
                <td>Retail Value</td>
                <td><strong>MRP (Listing Price)</strong></td>
                <td class="text-right">{{ number_format($stockValue->total_mrp, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Confidential Business Report - Authorized Personnel Only
    </div>
</body>
</html>
