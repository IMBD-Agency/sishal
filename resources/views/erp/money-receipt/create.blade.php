@extends('erp.master')

@section('title', 'New Money Receipt')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('money-receipt.index') }}" class="text-decoration-none text-muted">Money Receipt</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Create</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Record New Receipt</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('money-receipt.index') }}" class="btn btn-light border fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            {{-- Alert --}}
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif
             @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0 small fw-bold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('money-receipt.store') }}" method="POST" id="receiptForm">
                @csrf
                <div class="row g-4">
                    <!-- Left: Main Info -->
                    <div class="col-lg-12">
                        <div class="premium-card shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom p-4">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted small">
                                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Receipt Information
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Receipt Date <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Money Receipt No.</label>
                                        <input type="text" name="money_receipt_no" class="form-control bg-light border-0 fw-bold text-primary" value="{{ $receiptNo }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Payment Type <span class="text-danger">*</span></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="payment_type" id="customerBased" value="customer" checked>
                                            <label class="btn btn-outline-secondary" for="customerBased">
                                                <i class="fas fa-user me-1"></i> Customer Based
                                            </label>
                                            <input type="radio" class="btn-check" name="payment_type" id="invoiceBased" value="invoice">
                                            <label class="btn btn-outline-secondary" for="invoiceBased">
                                                <i class="fas fa-file-invoice me-1"></i> Invoice Based
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Customer Based Section -->
                                <div id="customerBasedSection" class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Customer <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select name="customer_id" id="customer_id" class="form-select select2-simple" required>
                                                <option value="">Search Customer...</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#newCustomerModal" title="Add New Customer">
                                                <i class="fas fa-plus"></i> New
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Select Invoice (Optional)</label>
                                        <select name="invoice_id" id="invoice_id" class="form-select select2-simple">
                                            <option value="">Select Invoice...</option>
                                        </select>
                                        <small class="text-muted">Leave blank for advance payment</small>
                                    </div>
                                </div>
                                
                                <!-- Invoice Based Section -->
                                <div id="invoiceBasedSection" class="row g-3 mt-2" style="display: none;">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Search Invoice <span class="text-danger">*</span></label>
                                        <select name="invoice_search_id" id="invoice_search_id" class="form-select select2-simple">
                                            <option value="">Search Invoice...</option>
                                            @if(isset($recentInvoices))
                                                @foreach($recentInvoices as $invoice)
                                                    @if($invoice->customer)
                                                        <option value="{{ $invoice->id }}" data-customer="{{ $invoice->customer_id }}">
                                                            {{ $invoice->invoice_number }} - {{ $invoice->customer->name }} (Due: {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Customer (Auto-filled)</label>
                                        <input type="text" id="autoCustomerName" class="form-control bg-light" readonly placeholder="Customer will be auto-filled">
                                        <input type="hidden" name="customer_id_hidden" id="customer_id_hidden">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Allocation Table -->
                        <div class="premium-card shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom p-4">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted small">
                                    <i class="fas fa-list me-2 text-primary"></i>Allocation Details
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table premium-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Invoice No.</th>
                                                <th class="text-end">Current Due</th>
                                                <th class="text-end">Previously Paid</th>
                                                <th class="text-center" style="width: 100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoiceTableBody">
                                            <tr id="emptyRow">
                                                <td colspan="4" class="text-center py-4 text-muted">Select an invoice to record payment</td>
                                            </tr>
                                            <tr id="invoiceRow" style="display: none;">
                                                <td id="td_invoice_no" class="fw-bold text-dark">-</td>
                                                <td id="td_due_amount" class="text-end text-danger fw-600">0.00</td>
                                                <td id="td_paid_amount" class="text-end text-success fw-600">0.00</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="resetTable()" style="width: 32px; height: 32px; padding: 0;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Finalize -->
                        <div class="premium-card shadow-sm border-0 bg-indigo-50">
                            <div class="card-body p-4">
                                <div class="row g-4 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">৳</span>
                                            <input type="number" step="0.01" name="amount" id="amount" class="form-control border-start-0 ps-0" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Method</label>
                                        <select name="payment_method" id="payment_method" class="form-select">
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank</option>
                                            <option value="mobile">Mobile</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Account <span class="text-danger">*</span></label>
                                        <select name="account_id" id="accountSelect" class="form-select" required>
                                            @foreach($bankAccounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ strtolower($acc->type) }}">
                                                    {{ $acc->provider_name }} {{ $acc->mobile_number ? '('.$acc->mobile_number.')' : ($acc->account_number ? '('.$acc->account_number.')' : '') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Note / Reference</label>
                                        <input type="text" name="note" class="form-control" placeholder="Optional notes...">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-create-premium w-100 py-2 fw-bold">
                                            <i class="fas fa-check-circle me-1"></i>Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-simple').select2({ width: '100%' });

            // Global Focus for Select2
            $(document).on('select2:open', () => {
                const searchField = document.querySelector('.select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            });

            // Handle payment type toggle
            $('input[name="payment_type"]').on('change', function() {
                const paymentType = $(this).val();
                
                if (paymentType === 'customer') {
                    $('#customerBasedSection').show();
                    $('#invoiceBasedSection').hide();
                    $('#customer_id').prop('required', true);
                    $('#invoice_search_id').prop('required', false);
                    resetTable();
                } else {
                    $('#customerBasedSection').hide();
                    $('#invoiceBasedSection').show();
                    $('#customer_id').prop('required', false);
                    $('#invoice_search_id').prop('required', true);
                    resetTable();
                }
            });

            // Handle customer change
            $('#customer_id').on('change', function(e, data) {
                const customerId = this.value;
                const invoiceSelect = $('#invoice_id');
                const targetInvoiceId = data ? data.targetInvoiceId : null;

                invoiceSelect.html('<option value="">Loading...</option>');
                
                if(!customerId) {
                    invoiceSelect.html('<option value="">Select Invoice...</option>');
                    resetTable();
                    return;
                }

                fetch(`{{ url('/erp/money-receipt/get-due-invoices') }}/${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        invoiceSelect.html('<option value="">Select Invoice...</option>');
                        data.forEach(inv => {
                            let selected = (targetInvoiceId == inv.id) ? 'selected' : '';
                            invoiceSelect.append(`<option value="${inv.id}" ${selected} data-number="${inv.invoice_number}" data-due="${inv.due_amount}" data-paid="${inv.paid_amount}">${inv.invoice_number} (Due: ${inv.due_amount})</option>`);
                        });
                        
                        // If we auto-selected an invoice, trigger its change event
                        if(targetInvoiceId) {
                            $('#invoice_id').val(targetInvoiceId).trigger('change');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching invoices:', error);
                        invoiceSelect.html('<option value="">Error loading invoices</option>');
                    });
            });

            // Handle invoice change (customer based)
            $('#invoice_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                
                if (!this.value) {
                    resetTable();
                    return;
                }
                
                const invoiceNumber = selectedOption.data('number');
                const dueAmount = parseFloat(selectedOption.data('due'));
                const paidAmount = parseFloat(selectedOption.data('paid'));
                
                $('#td_invoice_no').text(invoiceNumber);
                $('#td_due_amount').text(dueAmount.toFixed(2));
                $('#td_paid_amount').text(paidAmount.toFixed(2));
                
                $('#emptyRow').hide();
                $('#invoiceRow').show();
                
                // Auto-fill amount with due amount
                $('#amount').val(dueAmount);
            });

            // Handle invoice search change (invoice based)
            $('#invoice_search_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                
                if (!this.value) {
                    $('#autoCustomerName').val('');
                    $('#customer_id_hidden').val('');
                    resetTable();
                    return;
                }
                
                const customerId = selectedOption.data('customer');
                const optionText = selectedOption.text();
                
                // Parse the text: "INV-001 - Customer Name (Due: 1000.00)"
                const textParts = optionText.split(' - ');
                if (textParts.length >= 2) {
                    const customerName = textParts[1].split(' (Due:')[0];
                    $('#autoCustomerName').val(customerName.trim());
                }
                
                $('#customer_id_hidden').val(customerId);
                
                // Extract due amount from text
                const dueMatch = optionText.match(/Due: ([\d.,]+)/);
                const dueAmount = dueMatch ? parseFloat(dueMatch[1].replace(',', '')) : 0;
                
                // Update invoice table
                const invoiceNumber = textParts[0];
                $('#td_invoice_no').text(invoiceNumber);
                $('#td_due_amount').text(dueAmount.toFixed(2));
                $('#td_paid_amount').text('0.00');
                
                $('#emptyRow').hide();
                $('#invoiceRow').show();
                
                // Auto-fill amount with due amount
                $('#amount').val(dueAmount);
            });

            // Reset table function
            function resetTable() {
                $('#emptyRow').show();
                $('#invoiceRow').hide();
                $('#amount').val('');
            }

            // Form validation
            $('#receiptForm').on('submit', function(e) {
                const paymentType = $('input[name="payment_type"]:checked').val();
                
                if (paymentType === 'customer' && !$('#customer_id').val()) {
                    e.preventDefault();
                    alert('Please select a customer');
                    return false;
                }
                
                if (paymentType === 'invoice' && !$('#invoice_search_id').val()) {
                    e.preventDefault();
                    alert('Please select an invoice');
                    return false;
                }
                
                if (!$('#amount').val() || $('#amount').val() <= 0) {
                    e.preventDefault();
                    alert('Please enter a valid amount');
                    return false;
                }
                
                // For invoice-based, ensure customer_id_hidden is set
                if (paymentType === 'invoice' && !$('#customer_id_hidden').val()) {
                    e.preventDefault();
                    alert('Customer information missing. Please select invoice again.');
                    return false;
                }
                
                return true;
            });

            // Handle URL parameters for auto-selection from sale page
            function handleUrlParameters() {
                const urlParams = new URLSearchParams(window.location.search);
                const customerId = urlParams.get('customer_id');
                const invoiceId = urlParams.get('invoice_id');
                
                if (customerId && invoiceId) {
                    console.log('Auto-selecting customer:', customerId, 'invoice:', invoiceId);
                    
                    // Auto-select customer based payment type
                    $('#customerBased').prop('checked', true).trigger('change');
                    
                    // Wait for customer to load, then select invoice
                    setTimeout(() => {
                        $('#customer_id').val(customerId).trigger('change', { targetInvoiceId: invoiceId });
                    }, 300);
                } else if (invoiceId) {
                    console.log('Auto-selecting invoice only:', invoiceId);
                    
                    // If only invoice ID, use invoice-based selection
                    $('#invoiceBased').prop('checked', true).trigger('change');
                    
                    setTimeout(() => {
                        $('#invoice_search_id').val(invoiceId).trigger('change');
                    }, 300);
                }
            }

            // Initialize URL parameter handling after select2 is ready
            setTimeout(() => {
                handleUrlParameters();
            }, 100);

            // Handle new customer form submission
            $('#newCustomerForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#saveCustomerBtn').prop('disabled', true).text('SAVING...');
                const $err = $('#customerModalError').addClass('d-none');
                
                $.ajax({
                    url: "{{ route('customers.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: res => {
                        if (res.success && res.customer) {
                            const c = res.customer;
                            const label = (c.name || 'Unnamed') + ' (' + (c.phone || 'No Phone') + ')';
                            $('#customer_id').append(new Option(label, c.id, true, true)).trigger('change');
                            $('#newCustomerModal').modal('hide');
                            $('#newCustomerForm')[0].reset();
                        } else {
                            $err.text(res.message || 'Unknown error').removeClass('d-none');
                        }
                    },
                    error: xhr => {
                        let msg = 'Validation Error';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $err.text(msg).removeClass('d-none');
                    },
                    complete: () => {
                        $btn.prop('disabled', false).text('SAVE CUSTOMER');
                    }
                });
            });
        });
    </script>
@endsection

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 premium-card">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newCustomerForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label-premium">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Name (Optional if Phone exists)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Enter Phone (Optional if Name exists)">
                    </div>
                    <div id="customerModalError" class="text-danger small mb-2 d-none"></div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mt-2" id="saveCustomerBtn">SAVE CUSTOMER</button>
                </form>
            </div>
        </div>
    </div>
</div>
