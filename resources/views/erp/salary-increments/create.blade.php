@extends('erp.master')

@section('title', 'Apply Salary Increment')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="card premium-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">Apply Salary Increment</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('salary-increments.index') }}" class="text-decoration-none">List</a></li>
                                <li class="breadcrumb-item active">New Increment</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('salary-increments.store') }}" method="POST" id="incrementForm">
                        @csrf
                        <div class="row g-4">
                            <!-- Employee Selection -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Select Employee *</label>
                                <select name="employee_id" id="employee_id" class="form-select select2-premium-42" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->user->first_name }} {{ $emp->user->last_name }} ({{ $emp->phone }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Current Salary Display -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Current Salary</label>
                                <div class="form-control bg-light">
                                    <span class="fw-semibold text-primary">৳<span id="currentSalary">0.00</span></span>
                                </div>
                            </div>

                            <!-- New Salary -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">New Salary *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" name="new_salary" id="new_salary" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>

                            <!-- Increment Amount (Auto-calculated) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Increment Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" id="increment_amount" class="form-control bg-light" step="0.01" readonly>
                                </div>
                            </div>

                            <!-- Increment Percentage (Auto-calculated) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Increment Percentage</label>
                                <div class="input-group">
                                    <input type="number" id="increment_percentage" class="form-control bg-light" step="0.01" readonly>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <!-- Effective Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Effective Date *</label>
                                <input type="date" name="increment_effective_date" id="increment_effective_date" class="form-control" required>
                            </div>

                            <!-- Previous Increment Info -->
                            <div class="col-12" id="previousIncrementInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Previous Increment Details</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Last Increment:</strong> <span id="lastIncrementDate"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Previous Salary:</strong> ৳<span id="previousSalary"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Last Amount:</strong> ৳<span id="lastIncrementAmount"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Last %:</strong> <span id="lastIncrementPercentage"></span>%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Apply Increment
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
    // Set default effective date to next month
    const nextMonth = new Date();
    nextMonth.setMonth(nextMonth.getMonth() + 1);
    nextMonth.setDate(1);
    $('#increment_effective_date').val(nextMonth.toISOString().split('T')[0]);

    // Initialize select2
    $('.select2-premium-42').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Handle employee selection
    $('#employee_id').on('change', function() {
        const employeeId = $(this).val();
        if (employeeId) {
            $.ajax({
                url: `/erp/salary-increments/get-employee-salary-info/${employeeId}`,
                method: 'GET',
                success: function(response) {
                    $('#currentSalary').text(response.current_salary.toFixed(2));
                    $('#new_salary').val(response.current_salary);
                    calculateIncrement();
                    
                    // Show previous increment info if available
                    if (response.last_increment_date) {
                        $('#previousIncrementInfo').show();
                        $('#lastIncrementDate').text(response.last_increment_date);
                        $('#previousSalary').text(response.previous_salary.toFixed(2));
                        $('#lastIncrementAmount').text(response.increment_amount.toFixed(2));
                        $('#lastIncrementPercentage').text(response.increment_percentage.toFixed(1));
                    } else {
                        $('#previousIncrementInfo').hide();
                    }
                },
                error: function() {
                    $('#currentSalary').text('0.00');
                    $('#new_salary').val('');
                    $('#previousIncrementInfo').hide();
                }
            });
        } else {
            $('#currentSalary').text('0.00');
            $('#new_salary').val('');
            $('#previousIncrementInfo').hide();
        }
    });

    // Calculate increment on new salary change
    $('#new_salary').on('input', calculateIncrement);

    function calculateIncrement() {
        const currentSalary = parseFloat($('#currentSalary').text()) || 0;
        const newSalary = parseFloat($('#new_salary').val()) || 0;
        
        if (currentSalary > 0 && newSalary > 0) {
            const incrementAmount = newSalary - currentSalary;
            const incrementPercentage = (incrementAmount / currentSalary) * 100;
            
            $('#increment_amount').val(incrementAmount.toFixed(2));
            $('#increment_percentage').val(incrementPercentage.toFixed(2));
        } else {
            $('#increment_amount').val('');
            $('#increment_percentage').val('');
        }
    }
});
</script>
@endpush
