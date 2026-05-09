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
                        <h5 class="fw-bold text-dark mb-0">Sales Target Details</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('sales-targets.index') }}" class="text-decoration-none">List</a></li>
                                <li class="breadcrumb-item active">Details</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Target Information -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary bg-opacity-10">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-bullseye me-2"></i>Target Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="fw-bold">Employee:</td>
                                            <td>{{ $target->employee->user->first_name }} {{ $target->employee->user->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Period:</td>
                                            <td>{{ $target->period_month }} {{ $target->period_year }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Period Type:</td>
                                            <td><span class="badge bg-info">{{ ucfirst($target->period_type) }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Target Amount:</td>
                                            <td class="fw-semibold text-primary">৳{{ number_format($target->target_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Bonus Percentage:</td>
                                            <td>{{ $target->bonus_percentage }}%</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
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
                            <div class="card border-success">
                                <div class="card-header bg-success bg-opacity-10">
                                    <h6 class="mb-0 text-success">
                                        <i class="fas fa-chart-line me-2"></i>Achievement Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar {{ $target->is_achieved ? 'bg-success' : 'bg-warning' }}" 
                                                 style="width: {{ min($target->achievement_percentage, 100) }}%">
                                                {{ number_format($target->achievement_percentage, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="fw-bold">Achieved Amount:</td>
                                            <td class="fw-semibold {{ $target->is_achieved ? 'text-success' : 'text-warning' }}">
                                                ৳{{ number_format($target->achieved_amount, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Achievement %:</td>
                                            <td>{{ number_format($target->achievement_percentage, 2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Target Status:</td>
                                            <td>
                                                @if($target->is_achieved)
                                                    <span class="text-success fw-bold">
                                                        <i class="fas fa-check-circle me-1"></i>Achieved
                                                    </span>
                                                @else
                                                    <span class="text-warning fw-bold">
                                                        <i class="fas fa-clock me-1"></i>In Progress
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($target->is_achieved)
                                            <tr>
                                                <td class="fw-bold">Calculated Bonus:</td>
                                                <td class="fw-semibold text-success">
                                                    ৳{{ number_format($target->calculated_bonus, 2) }}
                                                </td>
                                            </tr>
                                        @endif
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
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="mb-0 text-info">
                                            <i class="fas fa-sticky-note me-2"></i>Notes
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $target->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Salary Payments Related to this Target -->
                    @if($target->salaryPayments->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary bg-opacity-10">
                                        <h6 class="mb-0 text-secondary">
                                            <i class="fas fa-money-bill-wave me-2"></i>Related Salary Payments
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Month</th>
                                                        <th>Year</th>
                                                        <th>Salary Amount</th>
                                                        <th>Bonus Amount</th>
                                                        <th>Total Payment</th>
                                                        <th>Payment Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($target->salaryPayments as $payment)
                                                        <tr>
                                                            <td>{{ $payment->month }}</td>
                                                            <td>{{ $payment->year }}</td>
                                                            <td>৳{{ number_format($payment->total_salary, 2) }}</td>
                                                            <td class="text-success">৳{{ number_format($payment->bonus_amount, 2) }}</td>
                                                            <td class="fw-semibold">৳{{ number_format($payment->total_payment, 2) }}</td>
                                                            <td>{{ date('d M Y', strtotime($payment->payment_date)) }}</td>
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
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="{{ route('sales-targets.edit', $target->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Target
                                </a>
                                <a href="javascript:void(0)" class="btn btn-success" onclick="updateAchievement({{ $target->id }})">
                                    <i class="fas fa-chart-line me-2"></i>Update Achievement
                                </a>
                                <a href="{{ route('sales-targets.index') }}" class="btn btn-secondary">
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
                    <h5 class="modal-title">Update Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="achievementForm">
                    <div class="modal-body">
                        <input type="hidden" id="targetId" name="target_id" value="{{ $target->id }}">
                        <div class="mb-3">
                            <label class="form-label">Achieved Amount</label>
                            <input type="number" step="0.01" class="form-control" id="achievedAmount" name="achieved_amount" value="{{ $target->achieved_amount }}" required>
                        </div>
                        <div id="achievementPreview" class="alert alert-info">
                            <strong>Current Achievement:</strong> {{ number_format($target->achievement_percentage, 1) }}%<br>
                            <strong>Current Bonus:</strong> ৳{{ number_format($target->calculated_bonus, 2) }}
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
function updateAchievement(targetId) {
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
