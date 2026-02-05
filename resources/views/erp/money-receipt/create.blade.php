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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
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
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Customer <span class="text-danger">*</span></label>
                                        <select name="customer_id" id="customer_id" class="form-select select2-simple" required>
                                            <option value="">Search Customer...</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Due Invoice</label>
                                        <select name="invoice_id" id="invoice_id" class="form-select select2-simple">
                                            <option value="">Select Invoice...</option>
                                        </select>
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
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Received Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">৳</span>
                                            <input type="number" step="0.01" name="amount" id="amount" class="form-control border-start-0 ps-0" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Payment Method</label>
                                        <select name="payment_method" class="form-select">
                                            <option value="Cash">Cash</option>
                                            <option value="Bank">Bank</option>
                                            <option value="Mobile Money">Mobile Money</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Account / Reference</label>
                                        <input type="text" name="note" class="form-control" placeholder="Optional notes...">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-create-premium w-100 py-2 fw-bold">
                                            <i class="fas fa-check-circle me-2"></i>Save Receipt
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

            // Handle customer change
            $('#customer_id').on('change', function() {
                const customerId = this.value;
                const invoiceSelect = $('#invoice_id');
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
                            invoiceSelect.append(`<option value="${inv.id}" data-number="${inv.invoice_number}" data-due="${inv.due_amount}" data-paid="${inv.paid_amount}">${inv.invoice_number} (Due: ${inv.due_amount})</option>`);
                        });
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        invoiceSelect.html('<option value="">Select Invoice...</option>');
                    });
            });

            $('#invoice_id').on('change', function() {
                 const selectedOption = $(this).find(':selected');
                 if(selectedOption.val()) {
                     $('#emptyRow').hide();
                     $('#invoiceRow').show();
                     $('#td_invoice_no').text(selectedOption.data('number'));
                     $('#td_due_amount').text(parseFloat(selectedOption.data('due')).toFixed(2));
                     $('#td_paid_amount').text(parseFloat(selectedOption.data('paid')).toFixed(2));
                     $('#amount').val(selectedOption.data('due')); 
                 } else {
                     resetTable();
                 }
            });
        });

        function resetTable() {
            $('#invoiceRow').hide();
            $('#emptyRow').show();
            $('#td_invoice_no').text('-');
            $('#td_due_amount').text('0.00');
            $('#td_paid_amount').text('0.00');
            $('#amount').val('');
        }
    </script>
    <style>
        .bg-indigo-50 { background-color: #f8faff; border: 1px dashed #dee2ff !important; }
        .fw-600 { font-weight: 600; }
        .premium-table thead th { background-color: #2d5a4c !important; color: white !important; font-size: 0.8rem; height: 45px; vertical-align: middle; }
    </style>
    @endpush
@endsection
