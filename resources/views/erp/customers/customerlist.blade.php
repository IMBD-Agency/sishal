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
                    <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fas fa-plus-circle"></i> <span>Add Customer</span>
                    </button>
                </div>
            </div>

            <!-- Modern Filter Card -->
            <!-- Premium Filter Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('customers.list') }}" id="filterForm">
                        <div class="row g-3">
                            <!-- Primary Filters Row -->
                            <div class="col-md-9">
                                <label class="form-label small text-muted fw-bold">General Search</label>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Search by Name, Email, Phone, or ID...">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm py-2">
                                    <i class="fas fa-filter me-2"></i>APPLY FILTERS
                                </button>
                            </div>

                            <!-- Advanced Details Row (Always Visible) -->
                            <div class="col-md-12 mt-2">
                                <hr class="my-3 opacity-10">
                            </div>

                            <div class="col-md-3 mt-0">
                                <label class="form-label small text-muted fw-bold">Branch</label>
                                <select class="form-select shadow-sm border-0 bg-light" name="branch_id">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-0">
                                <label class="form-label small text-muted fw-bold">Customer Source</label>
                                <select class="form-select shadow-sm border-0 bg-light" name="source">
                                    <option value="">All Sources</option>
                                    <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS / Store</option>
                                    <option value="online" {{ request('source') == 'online' ? 'selected' : '' }}>Online</option>
                                </select>
                            </div>
                            <div class="col-md-3 mt-0">
                                <label class="form-label small text-muted fw-bold">Customer Type</label>
                                <select class="form-select shadow-sm border-0 bg-light" name="premium">
                                    <option value="">All Types</option>
                                    <option value="1" {{ request('premium') == '1' ? 'selected' : '' }}>Premium Only</option>
                                    <option value="0" {{ request('premium') == '0' ? 'selected' : '' }}>Standard Only</option>
                                </select>
                            </div>
                            <div class="col-md-3 mt-0 d-flex align-items-end justify-content-end">
                                <a href="{{ route('customers.list') }}" class="btn btn-link text-danger text-decoration-none small fw-bold">
                                    <i class="fas fa-times-circle me-1"></i> RESET ALL
                                </a>
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
                                <th class="border-0 py-3 ps-4">Customer Info</th>
                                <th class="border-0 py-3">Contact Details</th>
                                <th class="border-0 py-3">Reference/Source</th>
                                <th class="border-0 py-3 text-center">Purchases</th>
                                <th class="border-0 py-3 text-center">Last Active</th>
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
                                    @if($customer->user_id)
                                        <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-3 rounded-pill">
                                            <i class="fas fa-globe me-1"></i> Online Web
                                        </span>
                                    @elseif($customer->pos_sales_count > 0)
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 rounded-pill">
                                            <i class="fas fa-store me-1"></i> POS / Retail
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-muted border border-secondary border-opacity-25 px-3 rounded-pill">
                                            <i class="fas fa-user-plus me-1"></i> Manual Entry
                                        </span>
                                    @endif
                                    <div class="small text-muted mt-1 ps-2">
                                        {{ $customer->city ?: 'No city' }}{{ $customer->country ? ', '.$customer->country : '' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="fw-bold text-dark">{{ $customer->pos_sales_count + $customer->invoices_count }}</span>
                                        <span class="extra-small text-muted text-uppercase">Total Orders</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($customer->posSales->count() > 0)
                                        <div class="small text-dark fw-medium">{{ $customer->posSales->first()->created_at->diffForHumans() }}</div>
                                        <div class="extra-small text-muted uppercase">{{ $customer->posSales->first()->created_at->format('d M, Y') }}</div>
                                    @else
                                        <span class="text-muted small">No Activity</span>
                                    @endif
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
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white border-0 py-3 px-4 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Create New Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCustomerForm">
                    <div class="modal-body p-4 p-xl-5">
                        <div class="row g-4">
                            <!-- Basic Essential Info -->
                            <div class="col-md-6 text-center text-md-start">
                                <h6 class="text-primary fw-bold text-uppercase small mb-3"><i class="fas fa-id-card me-2"></i>Essential Details</h6>
                            </div>
                            <div class="col-12 mt-0"></div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg border-2" name="name" placeholder="Customer Name" style="font-size: 0.95rem;" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg border-2" name="phone" placeholder="017-00000000" style="font-size: 0.95rem;" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-dark small">Address Line 1</label>
                                <input type="text" class="form-control border-2" name="address_1" placeholder="Street / Area">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small">City</label>
                                <input type="text" class="form-control border-2" name="city" placeholder="e.g. Dhaka">
                            </div>

                            <!-- Advanced Collapsible Section -->
                            <div class="col-12 mt-4">
                                <button class="btn btn-light w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 border" type="button" data-bs-toggle="collapse" data-bs-target="#moreCustomerOptions">
                                    <span class="fw-bold small text-muted">SHOW MORE ADVANCED OPTIONS (Optional)</span>
                                    <i class="fas fa-chevron-down small"></i>
                                </button>
                                
                                <div class="collapse" id="moreCustomerOptions">
                                    <div class="pt-4 row g-3">
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
                                        
                                        <!-- User Registration Section -->
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
                                                            <input type="password" class="form-control bg-white" id="user_password" name="user_password" minlength="6">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                                            <input type="password" class="form-control bg-white" id="user_password_confirmation" name="user_password_confirmation" minlength="6">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(function () {
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