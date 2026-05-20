@extends('erp.master')

@section('title', 'Sales Target Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="card premium-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">Branch Sales Target Details</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('sales-targets.index') }}" class="text-decoration-none">List</a></li>
                                <li class="breadcrumb-item active">Details</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Target Information -->
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary bg-opacity-10 py-3">
                                    <h6 class="mb-0 text-primary fw-bold">
                                        <i class="fas fa-bullseye me-2"></i>Target Configuration
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2" width="40%">Branch:</td>
                                            <td class="py-2">{{ $target->branch ? $target->branch->name : 'N/A' }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Period:</td>
                                            <td class="py-2">{{ $target->period_month }} {{ $target->period_year }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Period Type:</td>
                                            <td class="py-2"><span class="badge bg-info">{{ ucfirst($target->period_type) }}</span></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Target Quantity:</td>
                                            <td class="fw-semibold text-primary py-2">{{ number_format($target->target_quantity, 2) }} units</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Base Incentive:</td>
                                            <td class="py-2">৳{{ number_format($target->incentive_amount, 2) }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Comm. Per Extra Unit:</td>
                                            <td class="py-2">৳{{ number_format($target->commission_per_extra_sale, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold py-2">Status:</td>
                                            <td class="py-2">
                                                @if($target->status == 'achieved')
                                                    <span class="badge bg-success">Achieved</span>
                                                @elseif($target->status == 'active')
                                                    <span class="badge bg-primary">Active</span>
                                                @elseif($target->status == 'expired')
                                                    <span class="badge bg-secondary">Expired</span>
                                                @else
                                                    <span class="badge bg-warning">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Achievement Information -->
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success bg-opacity-10 py-3">
                                    <h6 class="mb-0 text-success fw-bold">
                                        <i class="fas fa-chart-line me-2"></i>Achievement & Bonus Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <div class="progress mb-1" style="height: 25px;">
                                            <div class="progress-bar {{ $target->is_achieved ? 'bg-success' : 'bg-warning' }}" 
                                                 style="width: {{ min($target->achievement_percentage, 100) }}%">
                                                {{ number_format($target->achievement_percentage, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">Target achievement progress</small>
                                    </div>
                                    
                                    <table class="table table-sm table-borderless">
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2" width="40%">Achieved Qty:</td>
                                            <td class="fw-semibold py-2 {{ $target->is_achieved ? 'text-success' : 'text-warning' }}">
                                                {{ number_format($target->achieved_quantity, 2) }} units
                                            </td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Achieved Incentive:</td>
                                            <td class="py-2">৳{{ number_format($target->achieved_incentive, 2) }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Extra Commission:</td>
                                            <td class="py-2">৳{{ number_format($target->achieved_extra_commission, 2) }}</td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-bold py-2">Total Branch Bonus:</td>
                                            <td class="fw-bold text-success py-2">
                                                ৳{{ number_format($target->total_achieved_bonus, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold py-2">Target Status:</td>
                                            <td class="py-2">
                                                @if($target->is_achieved)
                                                    <span class="text-success fw-bold">
                                                        <i class="fas fa-check-circle me-1"></i>Achieved
                                                    </span>
                                                @else
                                                    <span class="text-warning fw-bold">
                                                        <i class="fas fa-clock me-1"></i>In Progress (Need {{ number_format(max(0, $target->target_quantity - $target->achieved_quantity), 2) }} units more)
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    @if($target->notes)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info bg-opacity-10 py-2">
                                        <h6 class="mb-0 text-info fw-bold">
                                            <i class="fas fa-sticky-note me-2"></i>Notes
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0 text-muted">{{ $target->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Employee Bonus Distribution Breakdown -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary bg-opacity-10 py-3">
                                    <h6 class="mb-0 text-secondary fw-bold">
                                        <i class="fas fa-users-cog me-2"></i>Employee Bonus Distribution (Salary Share Breakdown)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Employee Name</th>
                                                    <th>Designation</th>
                                                    <th class="text-end">Monthly Salary</th>
                                                    <th class="text-end">Salary Share %</th>
                                                    <th class="text-end">Distributed Bonus</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $distributedTotal = 0;
                                                @endphp
                                                @forelse($branchEmployees as $employee)
                                                    @php
                                                        $sharePercentage = $totalBranchSalary > 0 ? ($employee->salary / $totalBranchSalary) * 100 : 0;
                                                        $employeeBonus = $totalBranchSalary > 0 ? ($target->total_achieved_bonus * $employee->salary) / $totalBranchSalary : 0;
                                                        $distributedTotal += $employeeBonus;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $employee->user->first_name }} {{ $employee->user->last_name }}</div>
                                                            <small class="text-muted">{{ $employee->phone }}</small>
                                                        </td>
                                                        <td>{{ $employee->designation }}</td>
                                                        <td class="text-end">৳{{ number_format($employee->salary, 2) }}</td>
                                                        <td class="text-end fw-semibold text-info">{{ number_format($sharePercentage, 2) }}%</td>
                                                        <td class="text-end fw-bold text-success">৳{{ number_format($employeeBonus, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-3">No active employees found in this branch to distribute target bonuses.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            @if($branchEmployees->count() > 0)
                                                <tfoot class="table-light fw-bold">
                                                    <tr>
                                                        <td colspan="2">Total Branch Stats</td>
                                                        <td class="text-end">৳{{ number_format($totalBranchSalary, 2) }}</td>
                                                        <td class="text-end text-info">100.00%</td>
                                                        <td class="text-end text-success">৳{{ number_format($distributedTotal, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Salary Payments -->
                    @if($target->salaryPayments->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-dark">
                                    <div class="card-header bg-dark bg-opacity-10 py-3">
                                        <h6 class="mb-0 text-dark fw-bold">
                                            <i class="fas fa-money-bill-wave me-2"></i>Related Salary Payments
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Designation</th>
                                                        <th>Salary Month</th>
                                                        <th class="text-end">Salary Amount</th>
                                                        <th class="text-end">Bonus Share</th>
                                                        <th class="text-end">Total Payment</th>
                                                        <th>Payment Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($target->salaryPayments as $payment)
                                                        <tr>
                                                            <td>{{ $payment->employee->user->first_name }} {{ $payment->employee->user->last_name }}</td>
                                                            <td>{{ $payment->employee->designation }}</td>
                                                            <td>{{ $payment->month }} {{ $payment->year }}</td>
                                                            <td class="text-end">৳{{ number_format($payment->total_salary, 2) }}</td>
                                                            <td class="text-end text-success fw-semibold">৳{{ number_format($payment->bonus_amount, 2) }}</td>
                                                            <td class="text-end fw-bold">৳{{ number_format($payment->total_payment, 2) }}</td>
                                                            <td>{{ $payment->payment_date ? date('d M Y', strtotime($payment->payment_date)) : 'Draft' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="{{ route('sales-targets.edit', $target->id) }}" class="btn btn-warning px-4 py-2 fw-bold text-dark">
                                    <i class="fas fa-edit me-2"></i>Edit Target
                                </a>
                                <a href="javascript:void(0)" class="btn btn-success px-4 py-2 fw-bold" onclick="updateAchievement({{ $target->id }}, {{ $target->achieved_quantity }})">
                                    <i class="fas fa-chart-line me-2"></i>Update Achievement
                                </a>
                                <a href="{{ route('sales-targets.index') }}" class="btn btn-secondary px-4 py-2 fw-bold">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Achievement Modal -->
    <div class="modal fade" id="updateAchievementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Sales Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="achievementForm">
                    <div class="modal-body">
                        <input type="hidden" id="targetId" name="target_id" value="{{ $target->id }}">
                        <div class="mb-3">
                            <label class="form-label">Achieved Quantity (units)</label>
                            <input type="number" step="0.01" class="form-control" id="achievedQuantity" name="achieved_quantity" value="{{ $target->achieved_quantity }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function updateAchievement(targetId, currentQty) {
    $('#updateAchievementModal').modal('show');
}

$('#achievementForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: `/erp/sales-targets/${$('#targetId').val()}/update-achievement`,
        method: 'POST',
        data: $(this).serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            location.reload();
        },
        error: function(xhr) {
            alert('Error updating achievement');
        }
    });
});
</script>
@endpush
