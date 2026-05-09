@extends('erp.master')

@section('title', 'Sales Targets Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb pe-3 mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Sales Targets</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Sales Targets Management</h4>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                            Target Registry
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
                                <li><a class="dropdown-item" href="{{ route('sales-targets.export.excel') }}">
                                    <i class="fas fa-file-excel me-2 text-success"></i>Export Excel
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('sales-targets.export.pdf') }}">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i>Export PDF
                                </a></li>
                            </ul>
                        </div>
                        <a href="{{ route('sales-targets.create') }}" class="btn btn-create-premium shadow-sm">
                            <i class="fas fa-bullseye me-2"></i>Set Target
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
                    <form action="{{ route('sales-targets.index') }}" method="GET" id="filterForm">
                        <div class="row g-3 mb-4">
                            @if(auth()->user()->hasRole('Super Admin'))
                            <div class="col-md-2">
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
                            @endif
                            <div class="col-md-{{ auth()->user()->hasRole('Super Admin') ? '2' : '3' }}">
                                <label class="form-label fw-bold small">Period Month</label>
                                <select name="period_month" class="form-select">
                                    <option value="">Select Month</option>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}" {{ request('period_month') == $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-{{ auth()->user()->hasRole('Super Admin') ? '2' : '3' }}">
                                <label class="form-label fw-bold small">Period Year</label>
                                <select name="period_year" class="form-select">
                                    <option value="">Select Year</option>
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ request('period_year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-{{ auth()->user()->hasRole('Super Admin') ? '2' : '3' }}">
                                <label class="form-label fw-bold small">Employee</label>
                                <select name="employee_id" class="form-select select2-premium-42">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->user->first_name }} {{ $employee->user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-{{ auth()->user()->hasRole('Super Admin') ? '2' : '3' }}">
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
                                    <th class="border-0">Employee</th>
                                    <th class="border-0">Branch</th>
                                    <th class="border-0">Period</th>
                                    <th class="border-0">Target Amount</th>
                                    <th class="border-0">Achieved</th>
                                    <th class="border-0">Achievement %</th>
                                    <th class="border-0">Bonus %</th>
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
                                                    <span class="text-primary fw-bold">{{ strtoupper(substr($target->employee->user->first_name, 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $target->employee->user->first_name }} {{ $target->employee->user->last_name }}</div>
                                                    <small class="text-muted">{{ $target->employee->designation }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ $target->branch ? $target->branch->name : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="fw-semibold">{{ $target->period_month }}</div>
                                            <small class="text-muted">{{ $target->period_year }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold text-primary">{{ number_format($target->target_amount, 2) }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="fw-semibold {{ $target->is_achieved ? 'text-success' : 'text-warning' }}">
                                                {{ number_format($target->achieved_amount, 2) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar {{ $target->is_achieved ? 'bg-success' : 'bg-warning' }}" 
                                                     style="width: {{ min($target->achievement_percentage, 100) }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ number_format($target->achievement_percentage, 1) }}%</small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-info bg-opacity-10 text-info">{{ $target->bonus_percentage }}%</span>
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
                                                <a href="{{ route('sales-targets.show', $target->id) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('sales-targets.edit', $target->id) }}" class="btn btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-success" onclick="updateAchievement({{ $target->id }})">
                                                    <i class="fas fa-chart-line"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteTarget({{ $target->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No sales targets found</p>
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
                    <h5 class="modal-title">Update Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="achievementForm">
                    <div class="modal-body">
                        <input type="hidden" id="targetId" name="target_id">
                        <div class="mb-3">
                            <label class="form-label">Achieved Amount</label>
                            <input type="number" step="0.01" class="form-control" id="achievedAmount" name="achieved_amount" required>
                        </div>
                        <div id="achievementPreview" class="alert alert-info" style="display: none;">
                            <strong>Achievement:</strong> <span id="achievementPercentage"></span>%<br>
                            <strong>Bonus Amount:</strong> <span id="bonusAmount"></span>
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
    $('#targetId').val(targetId);
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

$('#achievedAmount').on('input', function() {
    const targetId = $('#targetId').val();
    const achievedAmount = parseFloat($(this).value) || 0;
    
    if (achievedAmount > 0) {
        // This would need to be fetched from the server
        // For now, just show the input value
        $('#achievementPreview').show();
        $('#achievementPercentage').text('0');
        $('#bonusAmount').text('0');
    } else {
        $('#achievementPreview').hide();
    }
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
