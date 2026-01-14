@extends('erp.master')

@section('title', 'Supplier Payment')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">Supplier Payment</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}">Supplier Payment</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
            @endif

            <!-- Payment Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-primary">Supplier Payment Information</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('supplier-payments.store') }}" method="POST" id="paymentForm">
                        @csrf
                        
                        <!-- Top Row -->
                        <div class="row g-3 mb-4">
                            <!-- Payment Date -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Payment No (Auto-generated) -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Payment No.</label>
                                <input type="text" class="form-control bg-light" value="SP-{{ str_pad((App\Models\SupplierPayment::max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT) }}" readonly>
                            </div>

                            <!-- Select Supplier -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Select Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option value="">Select One</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $selectedSupplierId == $supplier->id ? 'selected' : '' }}
                                                data-balance="{{ $supplier->balance }}">
                                            {{ $supplier->name }} - Balance: {{ number_format($supplier->balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select Due Challan -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Select Due Challan <span class="text-danger">*</span></label>
                                <select name="purchase_bill_id" id="purchase_bill_id" class="form-select" required>
                                    <option value="">Select One</option>
                                    @if(!empty($bills))
                                        @foreach($bills as $bill)
                                            <option value="{{ $bill->id }}" data-due="{{ $bill->due_amount }}">
                                                {{ $bill->bill_number }} - Due: {{ number_format($bill->due_amount, 2) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Challan Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="bg-success text-white">
                                    <tr>
                                        <th>Challan No.</th>
                                        <th>Due Amount</th>
                                        <th>Paid Amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="challanTableBody">
                                    <tr class="text-center text-muted">
                                        <td colspan="4" class="py-4">No challan selected</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bottom Row -->
                        <div class="row g-3">
                            <!-- Total Amount -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Total Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" id="total_amount" class="form-control" placeholder="Amount" required readonly>
                            </div>

                            <!-- Account Type -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Account Type <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="">Select One</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="bkash">bKash</option>
                                    <option value="nagad">Nagad</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Account No -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Account No <span class="text-danger">*</span></label>
                                <input type="text" name="reference" class="form-control" placeholder="Account Number / Reference" required>
                            </div>

                            <!-- Note -->
                            <div class="col-12">
                                <label class="form-label fw-bold">Note</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="Note"></textarea>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-center mt-4">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-check me-2"></i>Submit
                            </button>
                            <a href="{{ route('supplier-payments.index') }}" class="btn btn-danger px-5">
                                <i class="fas fa-times me-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
        });
    </script>
    @endpush
@endsection
