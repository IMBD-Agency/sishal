@extends('erp.master')

@section('title', 'Branch Sales Targets')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">Branch Sales Targets</h1>
                <p class="text-muted mb-0 small">Manage and track monthly sales targets, branch incentives, and commissions for branches.</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('sales-targets.create') }}" class="btn btn-create-premium shadow-sm">
                    <i class="fas fa-plus me-2"></i>Create Branch Target
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Filter Card -->
            <div class="card premium-card report-filter-card mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('sales-targets.index') }}" method="GET" id="filterForm">
                        <div class="row g-3 mb-4">
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
                                <label class="form-label fw-bold small">Period Month</label>
                                <select name="period_month" class="form-select">
                                    <option value="">Select Month</option>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}" {{ request('period_month') == $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Period Year</label>
                                <select name="period_year" class="form-select">
                                    <option value="">Select Year</option>
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ request('period_year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="achieved" {{ request('status') == 'achieved' ? 'selected' : '' }}>Achieved</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div class="card-footer bg-light border-top p-3 mt-4 mx-n4 mb-n4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('sales-targets.export.excel', request()->all()) }}" class="btn btn-outline-success btn-sm fw-bold px-3 shadow-sm no-loader">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </a>
                                    <a href="{{ route('sales-targets.export.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm no-loader">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 shadow-sm no-loader" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('sales-targets.index') }}" class="btn btn-light border px-4 fw-bold text-muted justify-content-center" style="height: 42px; display: flex; align-items: center;">
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

            <!-- Targets Table -->
            <div class="card premium-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">ID</th>
                                    <th class="border-0">Branch</th>
                                    <th class="border-0">Period</th>
                                    <th class="border-0">Target Qty</th>
                                    <th class="border-0">Achieved Qty</th>
                                    <th class="border-0">Achievement %</th>
                                    <th class="border-0">Incentive (৳)</th>
                                    <th class="border-0">Comm./Extra (৳)</th>
                                    <th class="border-0">Total Bonus (৳)</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($targets as $target)
                                    <tr>
                                        <td class="align-middle">{{ $target->id }}</td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-primary fw-bold">{{ strtoupper(substr($target->branch ? $target->branch->name : 'B', 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $target->branch ? $target->branch->name : 'N/A' }}</div>
                                                    <small class="text-muted">Branch Target</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="fw-semibold">{{ $target->period_month }}</div>
                                            <small class="text-muted">{{ $target->period_year }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold text-primary">{{ number_format($target->target_quantity, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold {{ $target->is_achieved ? 'text-success' : 'text-warning' }}">
                                                {{ number_format($target->achieved_quantity, 2) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="progress mb-1" style="height: 6px;">
                                                <div class="progress-bar {{ $target->is_achieved ? 'bg-success' : 'bg-warning' }}" 
                                                     style="width: {{ min($target->achievement_percentage, 100) }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ number_format($target->achievement_percentage, 1) }}%</small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold">৳{{ number_format($target->incentive_amount, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold">৳{{ number_format($target->commission_per_extra_sale, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold">৳{{ number_format($target->total_achieved_bonus, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
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
                                        <td class="align-middle">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('sales-targets.show', $target->id) }}" class="btn btn-outline-primary" title="View details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('sales-targets.edit', $target->id) }}" class="btn btn-outline-warning" title="Edit target">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-success" onclick="updateAchievement({{ $target->id }}, {{ $target->achieved_quantity }})" title="Update sales volume">
                                                    <i class="fas fa-chart-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteTarget({{ $target->id }})" title="Delete target">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No branch sales targets found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($targets->hasPages())
                    <div class="card-footer bg-white">
                        {{ $targets->links() }}
                    </div>
                @endif
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
                        <input type="hidden" id="targetId" name="target_id">
                        <div class="mb-3">
                            <label class="form-label">Achieved Quantity (units)</label>
                            <input type="number" step="0.01" class="form-control" id="achievedQuantity" name="achieved_quantity" required>
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
    </div>
@endsection

@push('scripts')
<script>
function updateAchievement(targetId, currentQty) {
    $('#targetId').val(targetId);
    $('#achievedQuantity').val(currentQty);
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

function deleteTarget(targetId) {
    if (confirm('Are you sure you want to delete this sales target?')) {
        $.ajax({
            url: `/erp/sales-targets/${targetId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error deleting target');
            }
        });
    }
}
</script>
@endpush
