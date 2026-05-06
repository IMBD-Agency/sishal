@extends('erp.master')

@section('title', 'Edit Money Receipt')

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
                            <li class="breadcrumb-item active text-primary fw-600">Edit</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Edit Receipt #{{ $receipt->payment_reference }}</h4>
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

            <form action="{{ route('money-receipt.update', $receipt->id) }}" method="POST" id="receiptForm">
                @csrf
                @method('PUT')
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
                                        <input type="date" name="payment_date" class="form-control" value="{{ $receipt->payment_date }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Money Receipt No.</label>
                                        <input type="text" name="money_receipt_no" class="form-control bg-light border-0 fw-bold text-primary" value="{{ $receipt->payment_reference }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Customer <span class="text-danger">*</span></label>
                                        <select name="customer_id" id="customer_id" class="form-select select2-simple" required>
                                            <option value="">Search Customer...</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $receipt->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Due Invoice</label>
                                        <select name="invoice_id" id="invoice_id" class="form-select select2-simple">
                                            <option value="">Select Invoice...</option>
                                            @foreach($invoices as $inv)
                                                <option value="{{ $inv->id }}" {{ $receipt->invoice_id == $inv->id ? 'selected' : '' }} 
                                                        data-number="{{ $inv->invoice_number }}" 
                                                        data-due="{{ $inv->due_amount }}" 
                                                        data-paid="{{ $inv->paid_amount }}">
                                                    {{ $inv->invoice_number }} (Due: {{ $inv->due_amount }})
                                                </option>
                                            @endforeach
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
                                            <tr id="emptyRow" style="{{ $receipt->invoice_id ? 'display: none;' : '' }}">
                                                <td colspan="4" class="text-center py-4 text-muted">Select an invoice to record payment</td>
                                            </tr>
                                            <tr id="invoiceRow" style="{{ $receipt->invoice_id ? '' : 'display: none;' }}">
                                                <td id="td_invoice_no" class="fw-bold text-dark">{{ $receipt->invoice->invoice_number ?? '-' }}</td>
                                                <td id="td_due_amount" class="text-end text-danger fw-600">{{ $receipt->invoice->due_amount ?? '0.00' }}</td>
                                                <td id="td_paid_amount" class="text-end text-success fw-600">{{ $receipt->invoice->paid_amount ?? '0.00' }}</td>
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
                                            <input type="number" step="0.01" name="amount" id="amount" class="form-control border-start-0 ps-0" value="{{ $receipt->amount }}" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Method</label>
                                        <select name="payment_method" id="payment_method" class="form-select">
                                            <option value="cash" {{ $receipt->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="bank" {{ $receipt->payment_method == 'bank' ? 'selected' : '' }}>Bank</option>
                                            <option value="mobile" {{ $receipt->payment_method == 'mobile' ? 'selected' : '' }}>Mobile</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Account <span class="text-danger">*</span></label>
                                        <select name="account_id" id="accountSelect" class="form-select" required>
                                            @foreach($bankAccounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ strtolower($acc->type) }}" {{ $receipt->account_id == $acc->id ? 'selected' : '' }}>
                                                    {{ $acc->provider_name }} {{ $acc->mobile_number ? '('.$acc->mobile_number.')' : ($acc->account_number ? '('.$acc->account_number.')' : '') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Note / Reference</label>
                                        <input type="text" name="note" class="form-control" value="{{ $receipt->note }}" placeholder="Optional notes...">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-create-premium w-100 py-2 fw-bold">
                                            <i class="fas fa-save me-1"></i>Update
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
                        
                        if (targetInvoiceId) {
                            invoiceSelect.trigger('change');
                        }
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
                     // Only update amount if it was empty or matched old due
                     // For edit, maybe don't auto-update if they are just changing invoice but keep the amount?
                     // Let's auto-update for convenience.
                     $('#amount').val(selectedOption.data('due')); 
                 } else {
                     resetTable();
                 }
            });

            $('#payment_method').on('change', function() {
                let type = $(this).val().toLowerCase();
                let found = false;
                $('#accountSelect option').each(function() {
                    let optType = $(this).data('type');
                    if(optType === type) {
                        $(this).show();
                        if(!found) {
                            $(this).prop('selected', true);
                            found = true;
                        }
                    } else {
                        $(this).hide();
                    }
                });
            });
            // Initial trigger but preserve current selection if matches type
            const currentType = $('#payment_method').val();
            $('#accountSelect option').each(function() {
                if($(this).data('type') !== currentType) $(this).hide();
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
