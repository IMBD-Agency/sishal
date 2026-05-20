@extends('erp.master')

@section('title', 'Salary Increment Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Salary Increment Details</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('salary-increments.index') }}" class="text-decoration-none">List</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('salary-increments.index') }}" class="btn btn-outline-secondary px-3 me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="card premium-card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">Employee Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Name</label>
                            <h6 class="fw-bold">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Branch</label>
                            <h6 class="fw-bold">{{ $employee->branch ? $employee->branch->name : 'N/A' }}</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Phone</label>
                            <h6 class="fw-bold">{{ $employee->phone }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card premium-card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">Increment Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Previous Salary</label>
                            <h5 class="fw-bold">৳{{ number_format($employee->previous_salary, 2) }}</h5>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Current Salary</label>
                            <h5 class="fw-bold text-success">৳{{ number_format($employee->salary, 2) }}</h5>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Increment Amount</label>
                            <h5 class="fw-bold text-primary">+ ৳{{ number_format($employee->increment_amount, 2) }}</h5>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Increment Percentage</label>
                            <h5 class="fw-bold text-info">{{ number_format($employee->increment_percentage, 2) }}%</h5>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Last Increment Date</label>
                            <h6 class="fw-bold">{{ $employee->last_increment_date ? date('d M Y', strtotime($employee->last_increment_date)) : 'N/A' }}</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Effective From</label>
                            <h6 class="fw-bold">{{ $employee->increment_effective_date ? date('d M Y', strtotime($employee->increment_effective_date)) : 'N/A' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
