@extends('erp.master')

@section('title', 'Edit Branch Sales Target')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="card premium-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">Edit Branch Sales Target</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('sales-targets.index') }}" class="text-decoration-none">List</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('sales-targets.update', $target->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Branch Selection -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-primary">Branch *</label>
                                <select name="branch_id" class="form-select select2-premium-42" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $target->branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Period Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-primary">Period Type *</label>
                                <select name="period_type" class="form-select" required>
                                    <option value="monthly" {{ $target->period_type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ $target->period_type == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="yearly" {{ $target->period_type == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>

                            <!-- Period Month (for monthly targets) -->
                            <div class="col-md-6" id="monthField" style="{{ $target->period_type == 'monthly' ? '' : 'display: none;' }}">
                                <label class="form-label fw-bold small text-primary">Month *</label>
                                <select name="period_month" class="form-select">
                                    <option value="">Select Month</option>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}" {{ $target->period_month == $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Period Year -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-primary">Year *</label>
                                <select name="period_year" class="form-select" required>
                                    <option value="">Select Year</option>
                                    @for($i = date('Y') + 1; $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ $target->period_year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Target Quantity -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-primary">Target Quantity (Units) *</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="target_quantity" class="form-control" value="{{ $target->target_quantity }}" required placeholder="0.00">
                                    <span class="input-group-text">units</span>
                                </div>
                            </div>

                            <!-- Incentive Amount -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-primary">Incentive Amount (৳) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="incentive_amount" class="form-control" value="{{ $target->incentive_amount }}" required placeholder="0.00">
                                </div>
                            </div>

                            <!-- Commission Per Extra Sale -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-primary">Commission Per Extra Sale (৳) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="commission_per_extra_sale" class="form-control" value="{{ $target->commission_per_extra_sale }}" required placeholder="0.00">
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-primary">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" {{ $target->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="achieved" {{ $target->status == 'achieved' ? 'selected' : '' }}>Achieved</option>
                                    <option value="expired" {{ $target->status == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="inactive" {{ $target->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-primary">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes about this sales target...">{{ $target->notes }}</textarea>
                            </div>
                        </div>

                        <!-- Achievement Preview -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading fw-bold"><i class="fas fa-info-circle me-2"></i>Current Achievement Status</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Target Quantity:</strong> {{ number_format($target->target_quantity, 2) }} units
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Achieved Quantity:</strong> {{ number_format($target->achieved_quantity, 2) }} units
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Achievement %:</strong> {{ number_format($target->achievement_percentage, 1) }}%
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total Branch Bonus:</strong> ৳{{ number_format($target->total_achieved_bonus, 2) }}
                                        </div>
                                    </div>
                                    @if($target->is_achieved)
                                        <div class="mt-2 pt-2 border-top border-info border-opacity-20">
                                            <span class="badge bg-success">Target Achieved!</span>
                                            <span class="text-success fw-bold ms-2">Branch Incentive: ৳{{ number_format($target->achieved_incentive, 2) }}</span>
                                            <span class="text-success fw-bold ms-2">Extra Commission: ৳{{ number_format($target->achieved_extra_commission, 2) }}</span>
                                        </div>
                                    @else
                                        <div class="mt-2 pt-2 border-top border-info border-opacity-20">
                                            <span class="badge bg-warning">In Progress</span>
                                            <span class="text-muted ms-2">Branch has not achieved the target quantity yet. Bonus is currently 0.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-5 d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="fas fa-save me-2"></i>Update Target
                            </button>
                            <a href="{{ route('sales-targets.show', $target->id) }}" class="btn btn-info px-5 py-2 fw-bold text-white">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                            <a href="{{ route('sales-targets.index') }}" class="btn btn-secondary px-5 py-2 fw-bold">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
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
    // Period type change handler
    $('select[name="period_type"]').on('change', function() {
        var periodType = $(this).val();
        var monthField = $('#monthField');
        
        if (periodType === 'monthly') {
            monthField.show();
            monthField.find('select').prop('required', true);
        } else {
            monthField.hide();
            monthField.find('select').prop('required', false);
        }
    });

    // Initialize Select2
    $('.select2-premium-42').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});
</script>
@endpush
