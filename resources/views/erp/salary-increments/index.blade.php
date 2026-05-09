@extends('erp.master')

@section('title', 'Salary Increments')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb pe-3 mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Salary Increments</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Salary Increments</h4>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                            Increment History
                        </span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-2"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('salary-increments.export.excel') }}">
                                    <i class="fas fa-file-excel me-2 text-success"></i>Export Excel
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('salary-increments.export.pdf') }}">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i>Export PDF
                                </a></li>
                            </ul>
                        </div>
                        <a href="{{ route('salary-increments.create') }}" class="btn btn-create-premium shadow-sm">
                            <i class="fas fa-arrow-up me-2"></i>Apply Increment
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Filter Card -->
            <div class="card premium-card report-filter-card mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('salary-increments.index') }}" method="GET">
                        <div class="row g-3 mb-4">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Branch</label>
                                <select name="branch_id" class="form-select select2-premium-42">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Increment Month</label>
                                <input type="month" name="increment_month" class="form-control" value="{{ request('increment_month') }}">
                            </div>
                            @else
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Increment Month</label>
                                <input type="month" name="increment_month" class="form-control" value="{{ request('increment_month') }}">
                            </div>
                            @endif
                        </div>

                        <!-- Filter Actions -->
                        <div class="card-footer bg-light border-top p-3 mt-4 mx-n4 mb-n4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('salary-increments.export.excel', request()->all()) }}" class="btn btn-outline-success btn-sm fw-bold px-3 shadow-sm no-loader">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </a>
                                    <a href="{{ route('salary-increments.export.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm no-loader">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 shadow-sm no-loader" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('salary-increments.index') }}" class="btn btn-light border px-4 fw-bold text-muted justify-content-center" style="height: 42px; display: flex; align-items: center;">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </a>
                                    <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Increments Table -->
            <div class="card premium-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Employee</th>
                                    <th class="border-0">Branch</th>
                                    <th class="border-0">Previous Salary</th>
                                    <th class="border-0">Current Salary</th>
                                    <th class="border-0">Increment Amount</th>
                                    <th class="border-0">Increment %</th>
                                    <th class="border-0">Last Increment</th>
                                    <th class="border-0">Effective From</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-primary fw-bold">{{ strtoupper(substr($employee->user->first_name, 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</div>
                                                    <small class="text-muted">{{ $employee->designation }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ $employee->branch ? $employee->branch->name : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-muted">৳{{ number_format($employee->previous_salary, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold text-primary">৳{{ number_format($employee->salary, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold text-success">+৳{{ number_format($employee->increment_amount, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-success bg-opacity-10 text-success">{{ number_format($employee->increment_percentage, 1) }}%</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="small">
                                                <i class="fas fa-calendar text-muted me-1"></i>
                                                {{ $employee->last_increment_date ? date('d M Y', strtotime($employee->last_increment_date)) : 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="small">
                                                <i class="fas fa-clock text-info me-1"></i>
                                                {{ $employee->increment_effective_date ? date('d M Y', strtotime($employee->increment_effective_date)) : 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('salary-increments.show', $employee->id) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('salary-increments.edit', $employee->id) }}" class="btn btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-arrow-up fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No salary increments found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($employees->hasPages())
                    <div class="card-footer bg-white">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-premium-42').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});
</script>
@endpush
