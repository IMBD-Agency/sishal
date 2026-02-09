<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 30px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; font-size: 10px; line-height: 1.4; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .company-title { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0; text-transform: uppercase; }
        .report-title { font-size: 12px; color: #64748b; font-weight: bold; margin-top: 2px; }
        
        .result-box { background: #0f172a; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .result-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; margin-bottom: 5px; }
        .result-value { font-size: 24px; font-weight: bold; }
        .profit-bg { background: #059669; }
        .loss-bg { background: #dc2626; }

        .statement-table { width: 100%; border-collapse: collapse; }
        .statement-table th { background: #f8fafc; color: #475569; font-weight: bold; text-transform: uppercase; font-size: 9px; padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .statement-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .section-label { font-weight: bold; color: #334155; }
        .amount { font-family: 'Courier', monospace; font-weight: bold; text-align: right; font-size: 11px; }
        .text-success { color: #059669; }
        .text-danger { color: #dc2626; }
        
        .total-row { background: #f8fafc; font-weight: bold; }
        .footer-info { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 8px; color: #94a3b8; }
        .wealth-note { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 10px; margin-top: 20px; font-size: 9px; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <div style="float: left;">
            <h1 class="company-title">Operational Statement</h1>
            <div class="report-title">Monthly Business Profit & Loss Overview</div>
        </div>
        <div style="float: right; text-align: right;">
            <div style="color: #64748b; font-weight: bold;">PERIOD RANGE</div>
            <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table class="statement-table">
        <thead>
            <tr>
                <th width="50%">Revenue & Inflows</th>
                <th width="50%">Expenses & Outflows</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align: top; padding: 0; border-right: 1px solid #e2e8f0;">
                    <!-- INCOME TABLE -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Sales Revenue</div>
                                <div class="amount text-success">৳{{ number_format($salesAmount, 2) }}</div>
                            </td>
                        </tr>
                        @foreach($creditVoucherDetails as $detail)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">{{ $detail->name }}</div>
                                <div class="amount text-success">৳{{ number_format($detail->amount, 2) }}</div>
                            </td>
                        </tr>
                        @endforeach
                        @if($creditVoucherDetails->isEmpty() && $creditVoucher > 0)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Credit Vouchers</div>
                                <div class="amount text-success">৳{{ number_format($creditVoucher, 2) }}</div>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Money Receipts</div>
                                <div class="amount text-success">৳{{ number_format($moneyReceipt, 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Purchase Returns</div>
                                <div class="amount text-success">৳{{ number_format($purchaseReturnAmount, 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Exchange Adjustments</div>
                                <div class="amount text-success">৳{{ number_format($exchangeAmount, 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Transfers In</div>
                                <div class="amount text-success">৳{{ number_format($senderTransferAmount, 2) }}</div>
                            </td>
                        </tr>
                        <tr class="total-row">
                            <td style="padding: 12px; background: #f0fdf4;">
                                <span style="font-size: 8px; color: #166534; font-weight: bold;">TOTAL REVENUE</span>
                                <div class="amount text-success" style="font-size: 14px;">৳{{ number_format($totalIncome, 2) }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top; padding: 0;">
                    <!-- EXPENSE TABLE -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Cost of Goods Sold</div>
                                <div class="amount text-danger">৳{{ number_format($cogsAmount, 2) }}</div>
                            </td>
                        </tr>
                        @if($purchaseAmount > 0)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Purchase (Inventory)</div>
                                <div class="amount text-danger">৳{{ number_format($purchaseAmount, 2) }}</div>
                            </td>
                        </tr>
                        @endif
                        @foreach($debitVoucherDetails as $detail)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">{{ $detail->name }}</div>
                                <div class="amount text-danger">৳{{ number_format($detail->amount, 2) }}</div>
                            </td>
                        </tr>
                        @endforeach
                        @if($debitVoucherDetails->isEmpty() && $debitVoucher > 0)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Debit Vouchers</div>
                                <div class="amount text-danger">৳{{ number_format($debitVoucher, 2) }}</div>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Employee Salaries</div>
                                <div class="amount text-danger">৳{{ number_format($employeePayment, 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Supplier Payments</div>
                                <div class="amount text-danger">৳{{ number_format($supplierPay, 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Sales Returns</div>
                                <div class="amount text-danger">৳{{ number_format($salesReturnAmount, 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div class="section-label">Transfers Out</div>
                                <div class="amount text-danger">৳{{ number_format($receiverTransferAmount, 2) }}</div>
                            </td>
                        </tr>
                        <tr class="total-row">
                            <td style="padding: 12px; background: #fef2f2;">
                                <span style="font-size: 8px; color: #991b1b; font-weight: bold;">TOTAL EXPENSE</span>
                                <div class="amount text-danger" style="font-size: 14px;">৳{{ number_format($totalExpense, 2) }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 20px; text-align: center; background: {{ $netProfit >= 0 ? '#ecfdf5' : '#fef2f2' }}; border-top: 1px solid #0f172a;">
                    <div style="font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Statement Final Net Performance</div>
                    <div style="font-size: 22px; font-weight: bold; color: {{ $netProfit >= 0 ? '#059669' : '#dc2626' }};">
                        {{ $netProfit < 0 ? '-' : '' }}৳{{ number_format(abs($netProfit), 2) }}
                        <span style="font-size: 12px;">({{ $netProfit >= 0 ? 'Surplus / Profit' : 'Deficit / Loss' }})</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="wealth-note">
        <strong>INVENTORY STATUS:</strong> 
        Current valuation of stock assets (unrealized wealth) is <strong>৳{{ number_format($stockAmount, 2) }}</strong>. 
        This is not included in the cashflow calculation above.
    </div>

    <div class="footer-info">
        Document Generated: {{ date('F d, Y @ h:i A') }} • ERP Reporting System • Confidential
    </div>
</body>
</html>


