@extends('erp.master')

@section('title', 'Create Voucher')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">Voucher Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vouchers.store') }}" method="POST" id="voucherForm">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Voucher Date *</label>
                                <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Voucher No.</label>
                                <input type="text" name="voucher_no" class="form-control bg-light" value="{{ $voucherNo }}" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted">Voucher Type *</label>
                                <select name="voucher_type" class="form-select" required>
                                    <option value="Payment">Payment (Money Out)</option>
                                    <option value="Receipt">Receipt (Money In)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Transaction Category <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <select name="expense_account_id" class="form-select select2" required>
                                            <option value="">Select (e.g. Rent, Sales)</option>
                                            @foreach($expenseAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal" title="Add New Category">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="form-text small">
                                    Select the reason. <a href="{{ route('chart-of-account.index') }}" target="_blank" class="text-decoration-none">Manage Categories</a>
                                </div>
                            </div>
                        </div>

                        <!-- Particulars Table -->
                        <div class="table-responsive mb-4">
                            <table class="table border rounded overflow-hidden" id="particularsTable">
                                <thead class="bg-teal-header text-white">
                                    <tr>
                                        <th style="width: 60%">Description (Particulars)</th>
                                        <th style="width: 30%">Amount</th>
                                        <th style="width: 10%" class="text-center">Add</th>
                                    </tr>
                                </thead>
                                <tbody id="particularsBody">
                                    <tr class="particular-row">
                                        <td class="p-2">
                                            <input type="text" name="particulars[]" class="form-control border-0 bg-white" placeholder="e.g. Paid for January Rent" required>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="amounts[]" class="form-control border-0 bg-white text-end amount-input" placeholder="0.00" step="0.01" value="" required>
                                        </td>
                                        <td class="text-center p-2">
                                            <button type="button" class="btn btn-link text-primary p-0 text-decoration-none add-more-btn">Add More</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mb-4 g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Payment Method *</label>
                                <select name="account_type" class="form-select" id="accountTypeSelect">
                                    <option value="">Select (Cash/Bank)</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Payment Account (Wallet) *</label>
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <select name="account_id" class="form-select select2" required>
                                            <option value="">Select Source Account</option>
                                            @foreach($paymentAccounts as $pacc)
                                                <option value="{{ $pacc->id }}">{{ $pacc->name }} - {{ $pacc->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal" title="Add New Wallet">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="form-text small">
                                    Source/Dest account. <a href="{{ route('chart-of-account.index') }}" target="_blank" class="text-decoration-none">Manage Wallets</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Reference / Note</label>
                                <input type="text" name="note" class="form-control" placeholder="Any additional reference">
                            </div>
                        </div>

                        <!-- Hidden fields for related entities if needed (optional) -->
                        <div class="row mb-4 g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Select Branch (Outlet)</label>
                                <select name="branch_id" class="form-select select2">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Select Customer</label>
                                <select name="customer_id" class="form-select select2">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Select Supplier</label>
                                <select name="supplier_id" class="form-select select2">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="text-center py-4 border-top">
                            <button type="submit" class="btn btn-primary px-5 me-2">
                                <i class="fas fa-save me-2"></i>Submit
                            </button>
                            <a href="{{ route('vouchers.index') }}" class="btn btn-danger px-5">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding new Account Head -->
    <!-- Modal for adding new Account Head -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm"> <!-- Small modal for simplicity -->
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('chart-of-account.store') }}" method="POST" id="addAccountForm">
                    @csrf
                    <div class="modal-body pt-2">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Category Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Office Rent" autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">It is an...</label>
                            <select name="type_id" class="form-select" id="modalTypeSelect" required>
                                <option value="" disabled selected>Select Description</option>
                                @php $shownCats = []; @endphp
                                @foreach($accountTypes as $type)
                                    @php 
                                        $concept = '';
                                        if($type->name == 'Expense') $concept = 'expense';
                                        elseif($type->name == 'Revenue') $concept = 'income';
                                        elseif($type->name == 'Asset') $concept = 'asset';
                                        
                                        if(!$concept || in_array($concept, $shownCats)) continue;
                                        $shownCats[] = $concept;
                                    @endphp

                                    @if($concept == 'expense')
                                        <option value="{{ $type->id }}">Expense (Money Out)</option>
                                    @elseif($concept == 'income')
                                        <option value="{{ $type->id }}">Income (Money In)</option>
                                    @elseif($concept == 'asset')
                                        <option value="{{ $type->id }}">Cash / Bank Account (Wallet)</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- HIDDEN FIELDS (Auto-handled) -->
                        <div class="d-none">
                            <input type="text" name="code" value="{{ rand(10000, 99999) }}">
                            <input type="number" name="parent_id" id="modalParentId" value="">
                            <input type="number" name="sub_type_id" value="{{ $subTypes->first()->id ?? 1 }}">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill" id="saveAccountBtn">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const defaultParents = @json($defaultParents);

        $(document).ready(function() {
            // Initialize Select2
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: $(this).data('placeholder') || 'Select an option',
                    allowClear: true,
                    width: '100%'
                });
            });

            // Synchronize Code based on Type selection
            function generateRandomCode() {
                const typeId = $('#modalTypeSelect').val();
                if (!typeId) return;

                $.get('{{ url("erp/double-entry/get-next-code") }}/' + typeId, function(response) {
                    if (response.success) {
                        $('input[name="code"]').val(response.code);
                    }
                });
            }

            // Fetch code when selection changes
            $('#modalTypeSelect').on('change', generateRandomCode);
            // Auto-set parent based on Type selection
            function updateParent() {
                const typeId = $('#modalTypeSelect').val();
                if(defaultParents[typeId]) {
                    $('#modalParentId').val(defaultParents[typeId]);
                } else {
                     // Fallback: pick the first available or 1
                     $('#modalParentId').val(Object.values(defaultParents)[0] || 1);
                }
            }
            
            $('#modalTypeSelect').on('change', updateParent);
            updateParent(); // Init
            
            // Focus name input when modal opens & Regenerate Code
            $('#addAccountModal').on('shown.bs.modal', function () {
                $('input[name="name"]').focus();
                generateRandomCode(); // Generate a fresh code to avoid duplicates
            });

            // Dynamic row addition
            $('.add-more-btn').on('click', function() {
                const newRow = `
                    <tr class="particular-row">
                        <td class="p-2 border-top">
                            <input type="text" name="particulars[]" class="form-control border-0 bg-white" placeholder="Particulars" required>
                        </td>
                        <td class="p-2 border-top text-end">
                            <input type="number" name="amounts[]" class="form-control border-0 bg-white text-end amount-input" placeholder="Amount" step="0.01" value="0" required>
                        </td>
                        <td class="text-center p-2 border-top">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `;
                $('#particularsBody').append(newRow);
            });

            // Remove row
            $(document).on('click', '.remove-row-btn', function() {
                $(this).closest('tr').remove();
            });

            // Auto-focus amount
            $(document).on('keypress', 'input[name="particulars[]"]', function(e) {
                if(e.which == 13) {
                    e.preventDefault();
                    $(this).closest('tr').find('.amount-input').focus();
                }
            });

            // Payment Method helper
            $('#accountTypeSelect').on('change', function() {
                // Just a helper for the user, no strict hiding to avoid blocking
                const type = $(this).val();
                if(type) {
                   console.log("Selected Method: " + type);
                }
            });

            // AJAX Account Creation
            $('#addAccountForm').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = $('#saveAccountBtn');
                let originalText = btn.text();

                btn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if(response.success) {
                            // Add new option to dropdown
                            let newOption = new Option(response.data.name + ' (' + response.data.code + ')', response.data.id, true, true);
                            
                            // Determine target dropdown based on Type selection
                            let selectedTypeLabel = $('#modalTypeSelect option:selected').text();
                            let targetSelect = selectedTypeLabel.includes('Wallet') 
                                ? $('select[name="account_id"]') 
                                : $('select[name="expense_account_id"]');

                            targetSelect.append(newOption).val(response.data.id).trigger('change');
                            
                            // Force Select2 to refresh if it exists
                            if (targetSelect.hasClass('select2-hidden-accessible')) {
                                targetSelect.trigger('change.select2');
                            }

                            $('#addAccountModal').modal('hide');
                            form[0].reset();
                            updateParent(); // Reset parent logic
                            generateRandomCode(); // New code for next time
                            
                            // Optional: Toast message
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON.message || 'Something went wrong. Please check if Parent Group exists for this Type.'));
                    },
                    complete: function() {
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
