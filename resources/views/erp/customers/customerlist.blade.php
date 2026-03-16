@extends('erp.master')

@section('title', 'Customer Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <!-- Premium Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Customer Database</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Customer Management</h2>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('customers.export.excel', request()->query()) }}" class="btn btn-outline-success btn-sm fw-bold export-link-excel">
                            <i class="fas fa-file-excel me-1"></i>EXCEL
                        </a>
                        <a href="{{ route('customers.export.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm fw-bold export-link-pdf">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="fas fa-plus me-1"></i>ADD CUSTOMER
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Premium Filter Section -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-3">
                    <form id="filterForm" action="{{ route('customers.list') }}" method="GET" autocomplete="off">
                        <!-- Report Type Radios -->
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly</label>
                            </div>
                        </div>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">General Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, Email, Phone, ID..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                                <select name="branch_id" class="form-select form-select-sm select2">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Daily Fields -->
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-4 report-field monthly-group d-none">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                        <select name="month" class="form-select form-select-sm">
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ (request('month') ?? date('n')) == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                        <select name="year" class="form-select form-select-sm">
                                            @foreach(range(date('Y')-5, date('Y')+1) as $y)
                                                <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Yearly Fields -->
                            <div class="col-md-2 report-field yearly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select form-select-sm">
                                    @foreach(range(date('Y')-5, date('Y')+1) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Source</label>
                                <select name="source" class="form-select form-select-sm">
                                    <option value="">All Sources</option>
                                    <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS / Retail</option>
                                    <option value="online" {{ request('source') == 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual Entry</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Type</label>
                                <select name="premium" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="1" {{ request('premium') == '1' ? 'selected' : '' }}>Premium</option>
                                    <option value="0" {{ request('premium') == '0' ? 'selected' : '' }}>Standard</option>
                                </select>
                            </div>

                            <div class="col-md-auto ms-auto">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm">
                                        <i class="fas fa-filter me-1"></i>APPLY
                                    </button>
                                    <a href="{{ route('customers.list') }}" class="btn btn-light border btn-sm px-4 fw-bold shadow-sm">
                                        <i class="fas fa-undo me-1"></i>RESET
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="report-content-area">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="ps-4">Customer Info</th>
                                    <th>Contact Details</th>
                                    <th>Reference/Source</th>
                                    <th class="text-center">Purchases</th>
                                    <th class="text-center">Last Active</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3 bg-{{ ['primary','success','info','warning','danger'][($customer->id % 5)] }} text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 13px;">
                                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $customer->name }}
                                                    @if($customer->is_premium)
                                                        <i class="fas fa-star text-warning small ms-1" title="Premium Customer"></i>
                                                    @endif
                                                </div>
                                                <div class="small text-muted" style="font-size: 0.75rem;">ID: #{{ $customer->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column" style="font-size: 0.85rem;">
                                            <span class="text-dark"><i class="fas fa-phone-alt text-muted me-2 small"></i>{{ $customer->phone }}</span>
                                            <span class="text-muted small"><i class="fas fa-envelope text-muted me-2 small"></i>{{ $customer->email }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($customer->user_id)
                                            <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-3 rounded-pill" style="font-size: 0.7rem;">
                                                <i class="fas fa-globe me-1"></i> ONLINE WEB
                                            </span>
                                        @elseif($customer->pos_sales_count > 0)
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 rounded-pill" style="font-size: 0.7rem;">
                                                <i class="fas fa-store me-1"></i> POS / RETAIL
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-muted border border-secondary border-opacity-25 px-3 rounded-pill" style="font-size: 0.7rem;">
                                                <i class="fas fa-user-plus me-1"></i> MANUAL ENTRY
                                            </span>
                                        @endif
                                        <div class="small text-muted mt-1 ps-2" style="font-size: 0.75rem;">
                                            {{ $customer->city ?: 'No city' }}{{ $customer->country ? ', '.$customer->country : '' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="fw-bold text-dark">{{ $customer->pos_sales_count + $customer->invoices_count }}</span>
                                            <span class="text-muted text-uppercase" style="font-size: 0.65rem;">Total Orders</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($customer->posSales->count() > 0)
                                            <div class="small text-dark fw-medium">{{ $customer->posSales->first()->created_at->diffForHumans() }}</div>
                                            <div class="text-muted uppercase" style="font-size: 0.65rem;">{{ $customer->posSales->first()->created_at->format('d M, Y') }}</div>
                                        @else
                                            <span class="text-muted small">No Activity</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border shadow-sm rounded-circle px-2" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-3">
                                                <li><a class="dropdown-item py-2" href="{{ route('customer.show', $customer->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                                <li><a class="dropdown-item py-2" href="{{ route('customers.edit', $customer->id) }}"><i class="fas fa-edit me-2 text-info"></i>Edit Info</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('customers.destroy',$customer->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="fas fa-trash-alt me-2"></i>Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <div class="mb-3"><i class="fas fa-users-slash fa-3x opacity-25"></i></div>
                                            <h5 class="fw-bold">No customers found</h5>
                                            <p class="mb-0">Try adjusting your filters or add a new customer.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
                            </span>
                            {{ $customers->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white border-0 py-3 px-4 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Create New Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCustomerForm">
                    <div class="modal-body p-4 p-xl-5">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-2" name="name" placeholder="Customer Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-2" name="phone" placeholder="017-00000000" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-dark small">Address Line 1</label>
                                <input type="text" class="form-control border-2" name="address_1" placeholder="Street / Area">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small">City</label>
                                <input type="text" class="form-control border-2" name="city" placeholder="e.g. Dhaka">
                            </div>

                            <div class="col-12 mt-4">
                                <button class="btn btn-light w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 border" type="button" data-bs-toggle="collapse" data-bs-target="#moreCustomerOptions">
                                    <span class="fw-bold small text-muted">SHOW MORE ADVANCED OPTIONS (Optional)</span>
                                    <i class="fas fa-chevron-down small"></i>
                                </button>
                                
                                <div class="collapse" id="moreCustomerOptions">
                                    <div class="pt-3 row g-3">
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Email Address</label>
                                            <input type="email" class="form-control bg-light" name="email" placeholder="customer@email.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Tax / TIN Number</label>
                                            <input type="text" class="form-control bg-light" name="tax_number" placeholder="Tax ID">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Zip Code</label>
                                            <input type="text" class="form-control bg-light" name="zip_code" placeholder="Zip">
                                        </div>
                                        
                                        <div class="col-12 mt-3">
                                            <div class="p-3 bg-light rounded-3 border-start border-4 border-primary shadow-sm">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" value="1" id="register_as_user" name="register_as_user">
                                                    <label class="form-check-label fw-bold text-primary ms-2" for="register_as_user">Register as System User?</label>
                                                </div>
                                                <div id="userFields" class="mt-3 pt-3 border-top" style="display:none;">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">Choose Password <span class="text-danger">*</span></label>
                                                            <input type="password" class="password-field form-control bg-white" name="user_password" minlength="6">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                                            <input type="password" class="password-field form-control bg-white" name="user_password_confirmation" minlength="6">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="customerFormError" class="alert alert-danger d-none mt-4 border-0 shadow-sm"></div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary fw-bold px-5 py-2 shadow-sm">
                            <span class="btn-text">SAVE CUSTOMER</span>
                            <span class="btn-loading" style="display:none;"><i class="fas fa-circle-notch fa-spin me-2"></i> PROCESSING...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function toggleReportFields() {
        var reportType = $('.report-type-radio:checked').val();
        $('.report-field').addClass('d-none');
        
        if (reportType === 'daily') {
            $('.daily-group').removeClass('d-none');
        } else if (reportType === 'monthly') {
            $('.monthly-group').removeClass('d-none');
        } else if (reportType === 'yearly') {
            $('.yearly-group').removeClass('d-none');
        }
    }

    toggleReportFields();

    $('.report-type-radio').change(function() {
        const type = $(this).val();
        if (type === 'daily') {
            const today = new Date().toISOString().split('T')[0];
            $('#start_date').val(today);
            $('#end_date').val(today);
        }
        toggleReportFields();
    });

    function refreshCustomers() {
        const form = $('#filterForm');
        const container = $('#report-content-area');
        container.css('opacity', '0.5');
        
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            success: function(response) {
                const newContent = $(response).find('#report-content-area').html();
                if (newContent) {
                    container.html(newContent);
                } else {
                    container.html(response);
                }
                container.css('opacity', '1');
                
                // Update Excel/PDF links
                const queryParams = form.serialize();
                $('.export-link-excel').attr('href', '{{ route("customers.export.excel") }}?' + queryParams);
                $('.export-link-pdf').attr('href', '{{ route("customers.export.pdf") }}?' + queryParams);
            },
            error: function() {
                container.css('opacity', '1');
            }
        });
    }

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        refreshCustomers();
    });

    // Pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const container = $('#report-content-area');
        container.css('opacity', '0.5');
        
        $.ajax({
            url: url,
            success: function(response) {
                const newContent = $(response).find('#report-content-area').html();
                if (newContent) {
                    container.html(newContent);
                } else {
                    container.html(response);
                }
                container.css('opacity', '1');
                window.scrollTo(0, 0);
            }
        });
    });

    // Add Customer Logic
    $('#register_as_user').on('change', function () {
        if ($(this).is(':checked')) {
            $('#userFields').slideDown();
            $('.password-field').prop('required', true);
        } else {
            $('#userFields').slideUp();
            $('.password-field').prop('required', false);
        }
    });

    $('#addCustomerForm').on('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var errorBox = $('#customerFormError');
        errorBox.addClass('d-none').text('');
        btn.prop('disabled', true);
        form.find('.btn-text').hide();
        form.find('.btn-loading').show();

        $.ajax({
            url: '{{ route("customers.store") }}',
            method: 'POST',
            data: form.serialize() + '&_token={{ csrf_token() }}',
            success: function (res) {
                location.reload();
            },
            error: function (xhr) {
                let msg = 'An error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).map(function (arr) { return arr.join(' '); }).join(' ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                errorBox.removeClass('d-none').text(msg);
                btn.prop('disabled', false);
                form.find('.btn-text').show();
                form.find('.btn-loading').hide();
            }
        });
    });

    $('#addCustomerModal').on('hidden.bs.modal', function () {
        $('#addCustomerForm')[0].reset();
        $('#customerFormError').addClass('d-none').text('');
        $('#userFields').hide();
    });
});
</script>
@endpush