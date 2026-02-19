@extends('erp.master')

@section('title', 'Supplier Payment')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}" class="text-decoration-none text-muted">Suppliers</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Disbursement Entry</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Record Supplier Payment</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('supplier-payments.index') }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>History List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-wallet me-2 text-primary"></i>Disbursement Configuration</h6>
                        </div>
                        <div class="card-body p-4 p-xl-5">
                            <form action="{{ route('supplier-payments.store') }}" method="POST" id="paymentForm">
                                @csrf
                                
                                <div class="row g-4 mb-5">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-4 h-100">
                                            <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">1. Select Target Supplier <span class="text-danger">*</span></label>
                                            <select name="supplier_id" id="supplier_id" class="form-select select2 shadow-none" required>
                                                <option value="">Choose Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ $selectedSupplierId == $supplier->id ? 'selected' : '' }}
                                                            data-balance="{{ $supplier->balance }}">
                                                        {{ $supplier->name }} (Payable: {{ number_format($supplier->balance, 2) }}৳)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2 small text-muted">Select provider to retrieve pending obligations</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-4 h-100">
                                            <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">2. Search Bill/Invoice <span class="text-danger">*</span></label>
                                            <select name="purchase_bill_id" id="purchase_bill_id" class="form-select select2 shadow-none" required>
                                                <option value="">Search by Reference #</option>
                                                @if(!empty($bills))
                                                    @foreach($bills as $bill)
                                                        <option value="{{ $bill->id }}" data-due="{{ $bill->due_amount }}">
                                                            {{ $bill->bill_number }} (Due: {{ number_format($bill->due_amount, 2) }}৳)
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="mt-2 small text-muted">Only unpaid or partially paid bills are listed</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="premium-card mb-5 border-0 shadow-sm overflow-hidden">
                                    <div class="bg-dark p-3 d-flex justify-content-between align-items-center">
                                        <h6 class="text-white mb-0 small fw-bold text-uppercase"><i class="fas fa-list-check me-2 text-success"></i>Payment Allocation</h6>
                                        <div id="allocationBadge" class="badge bg-success rounded-pill px-3 py-2 fw-bold d-none">1 BILL SELECTED</div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table premium-table align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-4">Reference No.</th>
                                                    <th class="text-end">Balance Due</th>
                                                    <th class="text-center allocation-col">Payment Amount</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="challanTableBody">
                                                <tr class="text-center text-muted">
                                                    <td colspan="4" class="py-5">
                                                        <div class="opacity-25 mb-2"><i class="fas fa-hand-holding-dollar fa-3x"></i></div>
                                                        <p class="small mb-0 fw-bold">Select a bill from Step 2 to add for disbursement.</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-3">
                                        <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">Voucher Date</label>
                                        <input type="date" name="payment_date" class="form-control form-control-lg bg-light" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">Total Pay (৳)</label>
                                        <input type="number" step="0.01" name="amount" id="total_amount" class="form-control form-control-lg fw-bold text-primary bg-light" placeholder="0.00" required readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">Account Type <span class="text-danger">*</span></label>
                                        <select name="payment_method" id="payment_method" class="form-select" required>
                                            <option value="">Select Type</option>
                                            @php
                                                $availableTypes = $bankAccounts->pluck('type')->unique();
                                                $typeLabels = ['cash' => 'Cash', 'bank' => 'Bank Account', 'mobile' => 'Mobile Banking'];
                                            @endphp
                                            @foreach($typeLabels as $typeVal => $typeLabel)
                                                @if($availableTypes->contains($typeVal))
                                                    <option value="{{ $typeVal }}">{{ $typeLabel }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">Account <span class="text-danger">*</span></label>
                                        <select name="account_id" id="account_id" class="form-select" required>
                                            <option value="">Select Account</option>
                                            @foreach($bankAccounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                                    {{ $acc->provider_name }} — {{ $acc->account_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">Txn / Ref ID</label>
                                        <input type="text" name="reference" class="form-control" placeholder="Txn ID / Ref">
                                    </div>
                                    <div class="col-12 mt-4">
                                        <label class="form-label extra-small fw-bold text-muted text-uppercase mb-2">Transaction Memo</label>
                                        <textarea name="note" class="form-control border-dashed" rows="2" placeholder="Optional notes for internal audit..."></textarea>
                                    </div>
                                </div>

                                <div class="mt-5 text-center">
                                    <button type="submit" class="btn btn-create-premium px-5 py-3 rounded-pill fw-bold shadow-lg">
                                        <i class="fas fa-shield-check me-2"></i>CONFIRM DISBURSEMENT & RECORD
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .breadcrumb-premium { font-size: 0.85rem; }
        .allocation-col { width: 250px; }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let selectedChallans = [];

            // When supplier changes, reload to get their bills
            $('#supplier_id').on('change', function() {
                const supplierId = $(this).val();
                if (supplierId) {
                    window.location.href = `{{ route('supplier-payments.create') }}?supplier_id=${supplierId}`;
                } else {
                    $('#purchase_bill_id').html('<option value="">Select One</option>');
                    $('#challanTableBody').html('<tr class="text-center text-muted"><td colspan="4" class="py-4">No challan selected</td></tr>');
                }
            });

            // When challan is selected
            $('#purchase_bill_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const challanId = $(this).val();
                const challanNo = selectedOption.text().split(' - ')[0];
                const dueAmount = parseFloat(selectedOption.data('due')) || 0;

                if (challanId && !selectedChallans.find(c => c.id == challanId)) {
                    selectedChallans.push({
                        id: challanId,
                        number: challanNo,
                        due: dueAmount,
                        paid: dueAmount
                    });
                    
                    updateChallanTable();
                    updateTotalAmount();
                    
                    // Reset selection
                    $(this).val('');
                }
            });

            function updateChallanTable() {
                if (selectedChallans.length === 0) {
                    $('#challanTableBody').html('<tr class="text-center text-muted"><td colspan="4" class="py-4">No challan selected</td></tr>');
                    return;
                }

                let html = '';
                selectedChallans.forEach((challan, index) => {
                    html += `
                        <tr>
                            <td class="fw-bold">${challan.number}</td>
                            <td>${challan.due.toFixed(2)}</td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm paid-amount" 
                                       data-index="${index}" value="${challan.paid}" min="0" max="${challan.due}">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-challan" data-index="${index}">
                                    <i class="fas fa-trash fa-xs"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#challanTableBody').html(html);
            }

            function updateTotalAmount() {
                let total = 0;
                selectedChallans.forEach(challan => {
                    total += parseFloat(challan.paid) || 0;
                });
                $('#total_amount').val(total.toFixed(2));
            }

            // Update paid amount
            $(document).on('input', '.paid-amount', function() {
                const index = $(this).data('index');
                const value = parseFloat($(this).val()) || 0;
                const max = selectedChallans[index].due;
                
                if (value > max) {
                    $(this).val(max);
                    selectedChallans[index].paid = max;
                } else {
                    selectedChallans[index].paid = value;
                }
                
                updateTotalAmount();
            });

            // Remove challan
            $(document).on('click', '.remove-challan', function() {
                const index = $(this).data('index');
                selectedChallans.splice(index, 1);
                updateChallanTable();
                updateTotalAmount();
            });

            // Form submission - use the first selected challan
            $('#paymentForm').on('submit', function(e) {
                if (selectedChallans.length > 0) {
                    // Set the purchase_bill_id to the first selected challan
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'purchase_bill_id',
                        value: selectedChallans[0].id
                    }).appendTo(this);
                    
                    // Set amount to the paid amount for that challan
                    $('#total_amount').val(selectedChallans[0].paid);
                }
            });
            // Filter Account Dropdown based on Type
            $('#payment_method').on('change', function() {
                const type = $(this).val();
                const accountSelect = $('#account_id');
                
                accountSelect.val('').find('option').each(function() {
                    const optionType = $(this).data('type');
                    if (!type || !optionType || optionType === type) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Trigger once on load
            $('#payment_method').trigger('change');
        });
    </script>
    @endpush
