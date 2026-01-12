@extends('erp.master')

@section('title', 'Customer Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <!-- Header -->
        <div class="container-fluid px-4 py-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="h3 fw-bold mb-1 text-dark">Customer Database</h2>
                    <p class="text-muted mb-0">Manage your customer relationships and data.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.export.excel', request()->all()) }}" class="btn btn-success d-flex align-items-center gap-2 shadow-sm">
                        <i class="fas fa-file-excel"></i> <span class="d-none d-md-inline">Excel</span>
                    </a>
                    <a href="{{ route('customers.export.pdf', request()->all()) }}" class="btn btn-danger d-flex align-items-center gap-2 shadow-sm">
                        <i class="fas fa-file-pdf"></i> <span class="d-none d-md-inline">PDF</span>
                    </a>
                    <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" id="addCustomerBtn">
                        <i class="fas fa-plus-circle"></i> <span>Add Customer</span>
                    </button>
                </div>
            </div>

            <!-- Modern Filter Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('customers.list') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted fw-bold">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Name, Email, Phone...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted fw-bold">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted fw-bold">Type</label>
                                <select class="form-select" name="premium">
                                    <option value="">All Types</option>
                                    <option value="1" {{ request('premium') == '1' ? 'selected' : '' }}>Premium</option>
                                    <option value="0" {{ request('premium') == '0' ? 'selected' : '' }}>Standard</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted fw-bold">Date Range</label>
                                <input type="text" class="form-control" name="date_range" id="dateRange" value="{{ request('date_range') }}" placeholder="Select Dates">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-dark w-100 me-2">Apply</button>
                                <a href="{{ route('customers.list') }}" class="btn btn-light" title="Reset"><i class="fas fa-undo"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customer Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3 ps-4">Customer</th>
                                <th class="border-0 py-3">Contact</th>
                                <th class="border-0 py-3">Location</th>
                                <th class="border-0 py-3 text-center">Status</th>
                                <th class="border-0 py-3 text-center">Joined</th>
                                <th class="border-0 py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-{{ ['primary','success','info','warning','danger'][rand(0,4)] }} text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 14px;">
                                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $customer->name }}
                                                @if($customer->is_premium)
                                                    <i class="fas fa-star text-warning small ms-1" title="Premium Customer"></i>
                                                @endif
                                            </div>
                                            <div class="small text-muted">ID: #{{ $customer->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark"><i class="fas fa-phone-alt text-muted me-2 small"></i>{{ $customer->phone }}</span>
                                        <span class="text-muted small"><i class="fas fa-envelope text-muted me-2 small"></i>{{ $customer->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($customer->city || $customer->country)
                                        <div class="small text-dark">{{ $customer->city }}{{ $customer->city && $customer->country ? ', ' : '' }}{{ $customer->country }}</div>
                                        @if($customer->address_1)
                                            <div class="small text-muted text-truncate" style="max-width: 150px;">{{ $customer->address_1 }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $customer->is_active ? 'success-subtle text-success' : 'danger-subtle text-danger' }} rounded-pill px-3 border border-{{ $customer->is_active ? 'success' : 'danger' }} border-opacity-10">
                                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center text-muted small">
                                    {{ $customer->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                                            <li><a class="dropdown-item" href="{{ route('customer.show', $customer->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                            <li><a class="dropdown-item" href="{{ route('customers.edit', $customer->id) }}"><i class="fas fa-edit me-2 text-info"></i>Edit Info</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('customers.destroy',$customer->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Delete</button>
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

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCustomerForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="customer_email" name="email">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="register_as_user"
                                name="register_as_user">
                            <label class="form-check-label" for="register_as_user">Also register as user</label>
                        </div>
                        <div id="userFields" style="display:none;">
                            <div class="mb-3">
                                <label for="user_password" class="form-label">Password <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="user_password" name="user_password"
                                    minlength="6">
                            </div>
                            <div class="mb-3">
                                <label for="user_password_confirmation" class="form-label">Confirm Password <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="user_password_confirmation"
                                    name="user_password_confirmation" minlength="6">
                            </div>
                        </div>
                        <div id="customerFormError" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_tax_number" class="form-label">Tax Number</label>
                            <input type="text" class="form-control" id="customer_tax_number" name="tax_number">
                        </div>
                        <div class="mb-3">
                            <label for="customer_address_1" class="form-label">Address 1</label>
                            <input type="text" class="form-control" id="customer_address_1" name="address_1">
                        </div>
                        <div class="mb-3">
                            <label for="customer_address_2" class="form-label">Address 2</label>
                            <input type="text" class="form-control" id="customer_address_2" name="address_2">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="customer_city" name="city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_state" class="form-label">State</label>
                                <input type="text" class="form-control" id="customer_state" name="state">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="customer_country" name="country">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_zip_code" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="customer_zip_code" name="zip_code">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Add Customer</span>
                            <span class="btn-loading" style="display:none;"><i class="fas fa-spinner fa-spin"></i>
                                Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <script>
        $(function () {
            // Initialize Date Range Picker
            $('#dateRange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            // Show modal on button click
            $('#addCustomerBtn').on('click', function () {
                $('#addCustomerModal').modal('show');
            });

            // Show/hide user fields
            $('#register_as_user').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#userFields').slideDown();
                    $('#user_password, #user_password_confirmation').prop('required', true);
                } else {
                    $('#userFields').slideUp();
                    $('#user_password, #user_password_confirmation').prop('required', false);
                }
            });

            // Handle form submit
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
                    url: '{{ route('customers.store') }}',
                    method: 'POST',
                    data: form.serialize() + '&_token={{ csrf_token() }}',
                    success: function (res) {
                        // Success: reload or update list
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

            // Reset modal on close
            $('#addCustomerModal').on('hidden.bs.modal', function () {
                $('#addCustomerForm')[0].reset();
                $('#customerFormError').addClass('d-none').text('');
                $('#addCustomerForm button[type="submit"]').prop('disabled', false);
                $('#addCustomerForm .btn-text').show();
                $('#addCustomerForm .btn-loading').hide();
            });
        });
    </script>
@endsection