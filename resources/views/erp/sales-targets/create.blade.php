@extends('erp.master')

@section('title', 'Set Branch Sales Target')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="card premium-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">Set Branch Sales Target</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('sales-targets.index') }}" class="text-decoration-none">List</a></li>
                                <li class="breadcrumb-item active">New Target</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('sales-targets.store') }}" method="POST" id="targetForm">
                        @csrf
                        <div class="row g-4">
                            <!-- Branch Selection -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Select Branch *</label>
                                <select name="branch_id" id="branch_id" class="form-select select2-premium-42" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Period Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Period Type *</label>
                                <select name="period_type" id="period_type" class="form-select select2-premium-42" required>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>

                            <!-- Period Month (for monthly) -->
                            <div class="col-md-4" id="monthField">
                                <label class="form-label fw-bold small">Period Month *</label>
                                <select name="period_month" class="form-select select2-premium-42" required>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                                        <option value="{{ $m }}" {{ date('F') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Period Year -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Period Year *</label>
                                <select name="period_year" class="form-select select2-premium-42" required>
                                    @for($y = date('Y'); $y <= date('Y')+1; $y++)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Target Quantity -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Target Quantity (Units) *</label>
                                <div class="input-group">
                                    <input type="number" name="target_quantity" class="form-control" step="0.01" min="0" required placeholder="e.g. 500">
                                    <span class="input-group-text">units</span>
                                </div>
                            </div>

                            <!-- Incentive Amount -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Incentive Amount (৳) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" name="incentive_amount" class="form-control" step="0.01" min="0" required placeholder="e.g. 5000">
                                </div>
                                <small class="text-muted">Fixed bonus distributed to branch employees if target quantity is met</small>
                            </div>

                            <!-- Commission Per Extra Sale -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Commission Per Extra Sale (৳) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" name="commission_per_extra_sale" class="form-control" step="0.01" min="0" required placeholder="e.g. 10">
                                </div>
                                <small class="text-muted">Extra incentive per unit sold above target quantity</small>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes about this target..."></textarea>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Create Target
                                    </button>
                                    <a href="{{ route('sales-targets.index') }}" class="btn btn-outline-secondary">
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
    // Handle period type change
    $('#period_type').on('change', function() {
        const periodType = $(this).val();
        const monthField = $('#monthField');
        
        if (periodType === 'monthly') {
            monthField.show();
            monthField.find('select').prop('required', true);
        } else {
            monthField.hide();
            monthField.find('select').prop('required', false);
        }
    });

    // Initialize select2
    $('.select2-premium-42').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});
</script>
@endpush
