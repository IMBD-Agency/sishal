@extends('erp.master')

@section('title', 'Salary Payment Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Salary Payment Details</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('salary.index') }}" class="text-decoration-none">Staff Salary</a></li>
                            <li class="breadcrumb-item active">Payment #{{ $payment->id }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary px-3 me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                    <button class="btn btn-primary px-3 shadow-sm" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card premium-card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-primary">Payment Information</h5>
                                <span class="badge bg-success px-3 py-2">Success</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <p class="small text-muted text-uppercase fw-bold mb-2">Staff Details</p>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0">{{ $payment->employee->user->first_name }} {{ $payment->employee->user->last_name }}</h6>
                                                <p class="small text-muted mb-0">{{ $payment->employee->employee_id ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <p class="small text-muted text-uppercase fw-bold mb-2">Payment Schedule</p>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0">{{ $payment->month }} {{ $payment->year }}</h6>
                                                <p class="small text-muted mb-0">Salary Period</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-50">

                            <div class="row g-4">
                                <div class="col-sm-6 col-md-3">
                                    <label class="small text-muted text-uppercase fw-bold d-block mb-1">Total Salary</label>
                                    <h5 class="fw-bold">{{ number_format($payment->total_salary, 2) }}৳</h5>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <label class="small text-muted text-uppercase fw-bold d-block mb-1">Paid Amount</label>
                                    <h5 class="fw-bold text-success">{{ number_format($payment->paid_amount, 2) }}৳</h5>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <label class="small text-muted text-uppercase fw-bold d-block mb-1">Payment Date</label>
                                    <h6 class="mb-0">{{ date('d M Y', strtotime($payment->payment_date)) }}</h6>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <label class="small text-muted text-uppercase fw-bold d-block mb-1">Status</label>
                                    <h6 class="mb-0"><span class="text-success fw-bold">Paid</span></h6>
                                </div>
                            </div>

                            <div class="mt-4 p-3 border rounded-3 bg-white">
                                <label class="small text-muted text-uppercase fw-bold d-block mb-2">Payment Note</label>
                                <p class="mb-0 italic text-muted">{{ $payment->note ?: 'No specific notes for this payment.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card premium-card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="fw-bold mb-0 text-primary">Accounting & Audit</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <p class="small text-muted text-uppercase fw-bold mb-2">Debit From Account</p>
                                <div class="d-flex align-items-center p-3 border rounded-3">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">{{ $payment->chartOfAccount->name ?? 'N/A' }}</h6>
                                        <p class="small text-muted mb-0">Code: {{ $payment->chartOfAccount->code ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="small text-muted text-uppercase fw-bold mb-2">Payment Method</p>
                                <h6 class="fw-bold mb-0">{{ $payment->payment_method ?? 'Not Specified' }}</h6>
                            </div>

                            <div class="mb-4">
                                <p class="small text-muted text-uppercase fw-bold mb-2">Processed By</p>
                                <h6 class="fw-bold mb-0 d-flex align-items-center">
                                    <i class="fas fa-user-check text-success me-2"></i>
                                    {{ $payment->creator->first_name ?? 'System' }} {{ $payment->creator->last_name ?? '' }}
                                </h6>
                            </div>

                            <div>
                                <p class="small text-muted text-uppercase fw-bold mb-2">Transaction outlet</p>
                                <h6 class="fw-bold mb-0">{{ $payment->branch->name ?? 'Main Outlet' }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .btn, .main-sidebar, .main-header, .breadcrumb { display: none !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
            .main-content { padding: 0 !important; background: white !important; }
        }
    </style>
@endsection
