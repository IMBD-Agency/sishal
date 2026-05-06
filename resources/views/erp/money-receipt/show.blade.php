@extends('erp.master')

@section('title', 'Money Receipt - ' . $receipt->payment_reference)

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4 no-print">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-dark">Money Receipt Information</h4>
                <a href="{{ route('money-receipt.index') }}" class="btn btn-light border shadow-sm btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>

        <div class="container-fluid px-4 pb-5">
            <div class="receipt-card mx-auto bg-white p-5 rounded-0 shadow-sm" style="max-width: 1100px; border: 1px solid #dee2e6; color: #333;">
                
                {{-- Top Header: Logo and Company Info --}}
                <div class="row align-items-start mb-4">
                    <div class="col-8 d-flex align-items-center">
                        @php
                            $general_settings = \App\Models\GeneralSetting::first();
                        @endphp
                        @if($general_settings && $general_settings->logo)
                            <img src="{{ asset('storage/' . $general_settings->logo) }}" alt="Logo" style="max-height: 100px; margin-right: 20px;">
                        @endif
                        <div>
                            <h2 class="fw-bold mb-0" style="color: #2d5a4c; font-size: 1.8rem;">{{ $general_settings->site_title ?? 'Sisal Fashion' }}</h2>
                        </div>
                    </div>
                    <div class="col-4 text-end" style="font-size: 0.85rem; line-height: 1.4;">
                        <p class="mb-0">Address : {{ $general_settings->contact_address ?? 'N/A' }}</p>
                        <p class="mb-0">Contact No. : {{ $general_settings->contact_phone ?? 'N/A' }}</p>
                        <p class="mb-0">Email : {{ $general_settings->contact_email ?? 'N/A' }}</p>
                    </div>
                </div>

                <hr style="border-top: 1px solid #dee2e6; margin-top: 0;">

                {{-- Middle Section: Customer Info | Title | Receipt Info --}}
                <div class="row mb-3 align-items-center">
                    <div class="col-4">
                        <table class="table table-sm table-borderless mb-0" style="font-size: 0.9rem;">
                            <tr>
                                <td width="30%" class="py-1">Customer</td>
                                <td width="5%" class="py-1">:</td>
                                <td class="py-1 fw-bold">{{ $receipt->customer->name ?? 'Walk-in' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Address</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ $receipt->customer->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Mobile</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ $receipt->customer->phone ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-4 text-center">
                        <h4 class="fw-bold text-uppercase border-bottom d-inline-block pb-1 px-3" style="border-width: 2px !important;">Money Receipt</h4>
                    </div>
                    <div class="col-4">
                        <table class="table table-sm table-borderless mb-0" style="font-size: 0.9rem;">
                            <tr>
                                <td width="40%" class="py-1">Money Receipt No.</td>
                                <td width="5%" class="py-1">:</td>
                                <td class="py-1 fw-600">{{ $receipt->payment_reference }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Payment Mode</td>
                                <td class="py-1">:</td>
                                <td class="py-1 text-uppercase">{{ $receipt->payment_method ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Received Date</td>
                                <td class="py-1">:</td>
                                <td class="py-1">{{ \Carbon\Carbon::parse($receipt->payment_date)->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Created by</td>
                                <td class="py-1">:</td>
                                <td class="py-1 text-uppercase">{{ $receipt->creator->name ?? 'System' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Table Section --}}
                <div class="table-responsive mb-3">
                    <table class="table table-bordered receipt-table mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th width="5%" class="text-center">SL.</th>
                                <th width="15%">Invoice</th>
                                <th width="15%">Challan</th>
                                <th width="15%" class="text-center">Date</th>
                                <th width="25%">Products</th>
                                <th width="10%" class="text-center">Quantity</th>
                                <th width="15%" class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($receipt->invoice)
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>{{ $receipt->invoice->invoice_number }}</td>
                                    <td>{{ $receipt->invoice->challan_no ?? '-' }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($receipt->invoice->issue_date)->format('d-m-Y') }}</td>
                                    <td>
                                        @if($receipt->invoice->pos && $receipt->invoice->pos->items)
                                            @foreach($receipt->invoice->pos->items->take(2) as $item)
                                                {{ $item->product->name }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                            @if($receipt->invoice->pos->items->count() > 2) ... @endif
                                        @else
                                            General Payment
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $receipt->invoice->pos ? $receipt->invoice->pos->items->sum('quantity') : '-' }}
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($receipt->amount, 2) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-center">1</td>
                                    <td colspan="5" class="text-center">Account Payment / Advance</td>
                                    <td class="text-end fw-bold">{{ number_format($receipt->amount, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end fw-bold py-2">Total Amount</td>
                                <td class="text-end fw-bold py-2">{{ number_format($receipt->amount, 2) }}</td>
                            </tr>
                            <tr class="bg-light">
                                <td colspan="7" class="text-center py-2" style="font-style: italic; font-size: 0.9rem;">
                                    ( In Words : <span id="words_display"></span> Taka Only. )
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Note Section --}}
                <div class="row mb-5 mt-4">
                    <div class="col-12">
                        <p class="mb-1 fw-bold" style="font-size: 0.9rem;">Note / Remarks :</p>
                        <p class="text-muted small" style="min-height: 40px; border-bottom: 1px dotted #ccc;">
                            {{ $receipt->note ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- Signatures --}}
                <div class="row mt-5 pt-5 mb-4">
                    <div class="col-6 text-center">
                        <div class="signature-line mx-auto mb-2" style="border-top: 1px dotted #333; width: 200px;"></div>
                        <p class="mb-0 fw-bold small">Customer</p>
                    </div>
                    <div class="col-6 text-center">
                        <div class="signature-line mx-auto mb-2" style="border-top: 1px dotted #333; width: 200px;"></div>
                        <p class="mb-0 fw-bold small">Authorized By</p>
                    </div>
                </div>

                {{-- Print Buttons --}}
                <div class="text-center mt-5 no-print">
                    <button onclick="window.print()" class="btn btn-primary px-4 me-2">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                    <a href="{{ route('money-receipt.index') }}" class="btn btn-danger px-4">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function numberToWords(number) {
            var digit = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
            var elevenSeries = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
            var countingSeries = ['dummy', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
            var hundred = 'hundred';
            var thousand = 'thousand';
            var lakh = 'lakh';
            var crore = 'crore';

            number = parseFloat(number);
            var n_array = number.toString().split(".");
            var n = parseInt(n_array[0]);
            var res = "";

            if (n == 0) return "Zero";

            function convert_less_than_thousand(n) {
                var res = "";
                if (n >= 100) {
                    res += digit[Math.floor(n / 100)] + " " + hundred + " ";
                    n %= 100;
                }
                if (n >= 20) {
                    res += countingSeries[Math.floor(n / 10)] + " ";
                    n %= 10;
                }
                if (n >= 10) {
                    res += elevenSeries[n - 10] + " ";
                    n = 0;
                }
                if (n > 0) {
                    res += digit[n] + " ";
                }
                return res;
            }

            if (n >= 10000000) {
                res += convert_less_than_thousand(Math.floor(n / 10000000)) + crore + " ";
                n %= 10000000;
            }
            if (n >= 100000) {
                res += convert_less_than_thousand(Math.floor(n / 100000)) + lakh + " ";
                n %= 100000;
            }
            if (n >= 1000) {
                res += convert_less_than_thousand(Math.floor(n / 1000)) + thousand + " ";
                n %= 1000;
            }
            res += convert_less_than_thousand(n);
            
            return res.charAt(0).toUpperCase() + res.slice(1).trim();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const amount = {{ $receipt->amount }};
            document.getElementById('words_display').innerText = numberToWords(amount);
        });
    </script>

    <style>
        .receipt-table th, .receipt-table td {
            border: 1px solid #dee2e6 !important;
            padding: 10px !important;
            font-size: 0.9rem;
        }
        .receipt-table thead th {
            font-weight: bold;
            color: #333;
        }
        .fw-600 { font-weight: 600; }
        
        @media print {
            body { margin: 0; padding: 0; background: white !important; }
            .sidebar, .header, .no-print, nav, .breadcrumb { display: none !important; }
            .main-content { margin: 0 !important; padding: 0 !important; }
            .receipt-card { 
                border: none !important; 
                box-shadow: none !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                padding: 0 !important; 
                margin: 0 !important; 
            }
            .container-fluid { padding: 0 !important; }
            @page { margin: 1cm; size: landscape; }
            .receipt-table th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
        }
    </style>
@endsection
