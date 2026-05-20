@extends('erp.master')

@section('title', 'Edit Salary Increment')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="card premium-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">Edit Salary Increment</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('salary-increments.index') }}" class="text-decoration-none">List</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('salary-increments.update', $employee->id) }}" method="POST" id="incrementForm">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <!-- Employee Info -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Employee</label>
                                <div class="form-control bg-light">
                                    <span class="fw-semibold">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</span>
                                </div>
                            </div>

                            <!-- Current Salary Display -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Current Salary</label>
                                <div class="form-control bg-light">
                                    <span class="fw-semibold text-primary">৳<span id="currentSalary">{{ number_format($employee->salary, 2, '.', '') }}</span></span>
                                </div>
                            </div>

                            <!-- Increment Amount -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Increment Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" name="increment_amount" id="increment_amount" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>

                            <!-- New Salary (Auto-calculated/Manual) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">New Salary *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" name="new_salary" id="new_salary" class="form-control" step="0.01" min="0" required value="{{ number_format($employee->salary, 2, '.', '') }}">
                                </div>
                            </div>

                            <!-- Increment Percentage (Auto-calculated) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Increment Percentage</label>
                                <div class="input-group">
                                    <input type="number" name="increment_percentage" id="increment_percentage" class="form-control bg-light" step="0.01" readonly>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <!-- Effective Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Effective Date *</label>
                                <input type="date" name="increment_effective_date" id="increment_effective_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Increment
                                    </button>
                                    <a href="{{ route('salary-increments.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate when typing in the Increment Amount field
    $('#increment_amount').on('input', function() {
        if (!$(this).is(':focus')) return;

        const currentSalary = parseFloat($('#currentSalary').text()) || 0;
        const rawVal = $(this).val();
        
        if (currentSalary <= 0) return;
        
        if (rawVal === '') {
            $('#new_salary').val(currentSalary > 0 ? currentSalary : '');
            $('#increment_percentage').val('');
            return;
        }

        const incrementAmount = parseFloat(rawVal) || 0;
        const newSalary = currentSalary + incrementAmount;
        const percentage = (incrementAmount / currentSalary) * 100;
        
        $('#new_salary').val(newSalary > 0 ? parseFloat(newSalary.toFixed(2)) : '');
        $('#increment_percentage').val(percentage > 0 ? parseFloat(percentage.toFixed(2)) : '');
    });

    // Calculate when typing in the New Salary field
    $('#new_salary').on('input', function() {
        if (!$(this).is(':focus')) return;

        const currentSalary = parseFloat($('#currentSalary').text()) || 0;
        const rawVal = $(this).val();

        if (currentSalary <= 0) return;

        if (rawVal === '') {
            $('#increment_amount').val('');
            $('#increment_percentage').val('');
            return;
        }

        const newSalary = parseFloat(rawVal) || 0;
        if (newSalary >= currentSalary) {
            const incrementAmount = newSalary - currentSalary;
            const percentage = (incrementAmount / currentSalary) * 100;
            
            $('#increment_amount').val(incrementAmount > 0 ? parseFloat(incrementAmount.toFixed(2)) : '');
            $('#increment_percentage').val(incrementAmount > 0 ? parseFloat(percentage.toFixed(2)) : '');
        } else {
            $('#increment_amount').val('');
            $('#increment_percentage').val('');
        }
    });
});
</script>
@endpush
