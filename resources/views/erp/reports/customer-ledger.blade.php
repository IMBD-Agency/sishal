@extends('erp.master')

@section('title', 'Customer Ledger')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('reports.customer') }}" class="text-decoration-none text-muted">Customer Report</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Customer Ledger</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Customer Ledger</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-outline-dark shadow-sm fw-bold small" onclick="window.print()">
                        <i class="fas fa-print me-2 text-primary"></i> PRINT
                    </button>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            
            <!-- Filters Card -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-4">
                    <form method="GET" id="ledgerForm">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-book-open me-2 text-primary"></i>Selection and Filtering</h6>
                            
                            <div class="d-flex gap-3">
                                <div class="form-check cursor-pointer">
                                    <input class="form-check-input ledger-type cursor-pointer" type="radio" name="report_type" id="all" value="all" {{ $reportType == 'all' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted cursor-pointer" for="all">Customer All Ledger</label>
                                </div>
                                <div class="form-check cursor-pointer">
                                    <input class="form-check-input ledger-type cursor-pointer" type="radio" name="report_type" id="daily" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted cursor-pointer" for="daily">Daily Ledger</label>
                                </div>
                                <div class="form-check cursor-pointer">
                                    <input class="form-check-input ledger-type cursor-pointer" type="radio" name="report_type" id="monthly" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthly">Monthly Ledger</label>
                                </div>
                                <div class="form-check cursor-pointer">
                                    <input class="form-check-input ledger-type cursor-pointer" type="radio" name="report_type" id="yearly" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearly">Yearly Ledger</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Customer *</label>
                                <select name="customer_id" class="form-select form-select-sm select2-simple" required>
                                    <option value="">Select One</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ (isset($customer) && $customer->id == $c->id) ? 'selected' : '' }}>
                                            {{ $c->name }} {{ $c->phone ? "($c->phone)" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2 custom-date-group" style="{{ ($reportType != 'all') ? 'display:none;' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2 custom-date-group" style="{{ ($reportType != 'all') ? 'display:none;' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-create-premium btn-sm w-100" style="height: 31px;">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($customer))
                <!-- Ledger Content -->
                <div class="premium-card shadow-sm mt-4">
                    <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">{{ $customer->name }} - Detailed Ledger</h5>
                            <p class="text-muted small mb-0"><i class="fas fa-phone-alt me-1"></i> {{ $customer->phone }} | <i class="fas fa-map-marker-alt me-1"></i> {{ $customer->address ?? 'N/A' }}</p>
                        </div>
                        <div class="text-end">
                             <div class="px-3 py-2 bg-light rounded-3 d-inline-block border">
                                 <span class="small text-muted text-uppercase fw-bold d-block">Reporting Period</span>
                                 <span class="fw-bold">
                                     @if($startDate && $endDate)
                                         {{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}
                                     @else
                                         Full History
                                     @endif
                                 </span>
                             </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table premium-table table-bordered mb-0">
                                <thead class="bg-dark text-white text-uppercase small">
                                    <tr>
                                        <th class="ps-4 py-3">Date</th>
                                        <th class="py-3">Transaction Type / Note</th>
                                        <th class="py-3">Reference No</th>
                                        <th class="py-3 text-end">Debit (Bills)</th>
                                        <th class="py-3 text-end text-success">Credit (Pays)</th>
                                        <th class="py-3 text-end pe-4">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $runningBalance = $openingBalance ?? 0; @endphp
                                    
                                    @if($openingBalance > 0 || $openingBalance < 0)
                                        <tr class="bg-light-subtle">
                                            <td colspan="3" class="ps-4 fw-bold text-muted text-uppercase small">Opening Balance (Before Period)</td>
                                            <td colspan="2" class="text-end"></td>
                                            <td class="text-end pe-4 fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : 'Cr' }}
                                            </td>
                                        </tr>
                                    @endif

                                    @forelse($transactions as $txn)
                                        @php 
                                            $runningBalance += ($txn['debit'] - $txn['credit']);
                                        @endphp
                                        <tr>
                                            <td class="ps-4">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark">{{ $txn['type'] }}</span>
                                                    @if($txn['note'])
                                                        <span class="small text-muted italic">{{ $txn['note'] }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="font-monospace small text-primary fw-600">{{ $txn['reference'] }}</td>
                                            <td class="text-end">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                                            <td class="text-end text-success">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                                            <td class="text-end pe-4 fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : 'Cr' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fas fa-info-circle me-2"></i> No transactions found for this customer in selected period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="5" class="text-end ps-4 text-uppercase">Closing Balance</td>
                                        <td class="text-end pe-4 {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }} fs-6">
                                            {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : 'Cr' }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="premium-card p-5 text-center shadow-sm">
                    <div class="py-5">
                        <i class="fas fa-search-dollar fa-4x text-muted mb-4 opacity-25"></i>
                        <h5 class="fw-bold text-muted">Search for a Customer to View Ledger</h5>
                        <p class="text-muted small">Select a customer from the dropdown above to display their transaction history, payments, and returns.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-simple').select2({
                width: '100%'
            });

            $('.ledger-type').on('change', function() {
                if ($(this).val() === 'all') {
                    $('.custom-date-group').show();
                } else {
                    $('.custom-date-group').hide();
                }
            });
        });
    </script>
    @endpush
@endsection
