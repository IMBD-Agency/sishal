@extends('erp.master')

@section('title', 'Trial Balance')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Reports</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Trial Balance</h4>
                    <p class="text-muted small mb-0">Financial position as of {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
                </div>
                <div class="col-md-5 text-end">
                    <button class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-download me-2"></i>Export Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="container-fluid px-4 py-4">
            
            <!-- Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('trialBalance.index') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="end_date" class="form-label text-muted small fw-bold">As of Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input type="date" class="form-control border-start-0 ps-0" id="end_date" name="end_date" 
                                           value="{{ request('end_date') ?? date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Trial Balance Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-balance-scale me-2"></i>Statement of Accounts
                        </h5>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-uppercase text-muted small fw-bold" style="width: 15%">Code</th>
                                <th class="text-uppercase text-muted small fw-bold" style="width: 40%">Account Name</th>
                                <th class="text-uppercase text-muted small fw-bold" style="width: 15%">Type</th>
                                <th class="text-end text-uppercase text-muted small fw-bold" style="width: 15%">Debit (৳)</th>
                                <th class="text-end pe-4 text-uppercase text-muted small fw-bold" style="width: 15%">Credit (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">{{ $account['code'] }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $account['name'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border">
                                            {{ $account['type_name'] }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold {{ $account['total_debit'] > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                                        {{ number_format($account['total_debit'], 2) }}
                                    </td>
                                    <td class="text-end pe-4 fw-bold {{ $account['total_credit'] > 0 ? 'text-dark' : 'text-muted opacity-50' }}">
                                        {{ number_format($account['total_credit'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-light rounded-circle p-4 mb-3">
                                                <i class="fas fa-file-invoice-dollar fa-3x text-muted opacity-50"></i>
                                            </div>
                                            <h5 class="text-muted">No records found</h5>
                                            <p class="text-muted small mb-0">Try changing the date filter to see more data.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light">
                            <tr class="border-top border-2 border-secondary">
                                <td colspan="3" class="ps-4 py-3 text-end fw-bold text-uppercase">Total</td>
                                <td class="text-end py-3 fw-bold fs-6 {{ $totalDebit == $totalCredit ? 'text-success' : 'text-danger' }}">
                                    ৳{{ number_format($totalDebit, 2) }}
                                </td>
                                <td class="text-end pe-4 py-3 fw-bold fs-6 {{ $totalDebit == $totalCredit ? 'text-success' : 'text-danger' }}">
                                    ৳{{ number_format($totalCredit, 2) }}
                                </td>
                            </tr>
                            @if($totalDebit != $totalCredit)
                            <tr>
                                <td colspan="5" class="text-center py-2 bg-danger bg-opacity-10 text-danger fw-bold small">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Trial Balance is NOT balanced! Difference: ৳{{ number_format(abs($totalDebit - $totalCredit), 2) }}
                                </td>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection