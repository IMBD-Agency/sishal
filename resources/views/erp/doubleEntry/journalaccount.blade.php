@extends('erp.master')

@section('title', 'Journal Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Accounting & Finance</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Journal Entries</h4>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                            Double Entry Log
                        </span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button type="button" class="btn btn-light border shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addJournalModal">
                        <i class="fas fa-plus me-2 text-primary"></i>New Journal
                    </button>
                    <button class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-download me-2"></i>Export Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('journal.list') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label text-muted small fw-bold">From Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input type="date" class="form-control border-start-0 ps-0" id="start_date" name="start_date" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label text-muted small fw-bold">To Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    <input type="date" class="form-control border-start-0 ps-0" id="end_date" name="end_date" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label text-muted small fw-bold">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="search" name="search" 
                                           placeholder="Voucher No, Memo..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-filter me-2"></i>Filter
                                    </button>
                                    <a href="{{ route('journal.list') }}" class="btn btn-light border flex-grow-1" title="Reset Filters">
                                        <i class="fas fa-undo text-secondary"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Journal Summary Bar -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="premium-card bg-primary bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-primary text-white rounded-3 p-2">
                                <i class="fas fa-book fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-primary opacity-75">Total Journals</div>
                                <div class="h5 mb-0 fw-bold text-primary">{{ $journals->count() ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card bg-success bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-success text-white rounded-3 p-2">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-success opacity-75">Total Debit</div>
                                <div class="h5 mb-0 fw-bold text-success">৳{{ number_format($journals->sum('total_debit') ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card bg-warning bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-warning text-white rounded-3 p-2">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-warning opacity-75">Total Credit</div>
                                <div class="h5 mb-0 fw-bold text-warning">৳{{ number_format($journals->sum('total_credit') ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card bg-info bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-info text-white rounded-3 p-2">
                                <i class="fas fa-balance-scale fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-info opacity-75">Balanced Journals</div>
                                <div class="h5 mb-0 fw-bold text-info">
                                    {{ $journals->filter(fn($j) => $j->isBalanced())->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journal Entries Table -->
            <div class="premium-card shadow-sm mb-5">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">Voucher No</th>
                                    <th>Date</th>
                                    <th>Memo</th>
                                    <th>Type</th>
                                    <th class="text-end">Total Debit</th>
                                    <th class="text-end">Total Credit</th>
                                    <th class="text-center">Status</th>
                                    <th>Created By</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                                    <tbody>
                                        @forelse($journals ?? [] as $journal)
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1">{{ $journal->voucher_no }}</span>
                                                </td>
                                                <td class="text-dark fw-500">{{ $journal->entry_date->format('d M, Y') }}</td>
                                                <td>
                                                    <div class="text-truncate text-muted small" style="max-width: 250px;" title="{{ $journal->description }}">
                                                        {{ $journal->description ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($journal->type)
                                                        @php
                                                            $typeColors = [
                                                                'Journal' => 'bg-primary',
                                                                'Payment' => 'bg-success',
                                                                'Receipt' => 'bg-info',
                                                                'Contra' => 'bg-warning',
                                                                'Adjustment' => 'bg-secondary'
                                                            ];
                                                            $color = $typeColors[$journal->type] ?? 'bg-secondary';
                                                        @endphp
                                                        <span class="badge {{ $color }} bg-opacity-10 text-{{ str_replace('bg-', '', $color) }} border border-{{ str_replace('bg-', '', $color) }} border-opacity-25 px-2 py-1">
                                                            {{ $journal->type }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-dark border px-2 py-1">General</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold text-success">৳{{ number_format($journal->total_debit, 2) }}</td>
                                                <td class="text-end fw-bold text-warning">৳{{ number_format($journal->total_credit, 2) }}</td>
                                                <td class="text-center">
                                                    @if($journal->isBalanced())
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">
                                                            <i class="fas fa-check-circle me-1"></i>Balanced
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-1 px-2">
                                                            <i class="fas fa-times-circle me-1"></i>Unbalanced
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="small text-muted">
                                                    {{ $journal->createdBy ? ($journal->createdBy->first_name . ' ' . $journal->createdBy->last_name) : 'N/A' }}
                                                </td>
                                                <td class="text-center pe-4">
                                                    <div class="btn-group btn-group-sm rounded-3 overflow-hidden border shadow-sm">
                                                        <a href="{{ route('journal.show', $journal->id) }}" class="btn btn-white text-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-white text-info"
                                                            onclick="editJournal({{ $journal->id }}, '{{ $journal->entry_date->format('Y-m-d') }}', '{{ addslashes($journal->description) }}', '{{ $journal->type }}')"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-white text-danger delete-journal-btn"
                                                            data-journal-id="{{ $journal->id }}"
                                                            data-voucher-no="{{ $journal->voucher_no }}"
                                                            title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">
                                                    <i class="fas fa-book fa-2x mb-2"></i>
                                                    <h6>No Journal Entries Found</h6>
                                                    <p>Create your first journal entry to get started.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Journal Modal -->
    <div class="modal fade" id="addJournalModal" tabindex="-1" aria-labelledby="addJournalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addJournalModalLabel">New Journal Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="journalForm" action="{{ route('journal.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="entry_date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('entry_date') is-invalid @enderror"
                                    id="entry_date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}"
                                    required>
                                @error('entry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="type" class="form-label">Journal Type</label>
                                <select class="form-control @error('type') is-invalid @enderror" id="type" name="type">
                                    <option value="">Select Type</option>
                                    <option value="Journal" {{ old('type') == 'Journal' ? 'selected' : '' }}>Journal</option>
                                    <option value="Payment" {{ old('type') == 'Payment' ? 'selected' : '' }}>Payment</option>
                                    <option value="Receipt" {{ old('type') == 'Receipt' ? 'selected' : '' }}>Receipt</option>
                                    <option value="Contra" {{ old('type') == 'Contra' ? 'selected' : '' }}>Contra</option>
                                    <option value="Adjustment" {{ old('type') == 'Adjustment' ? 'selected' : '' }}>Adjustment
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Balance Status</label>
                                <div class="d-flex align-items-center">
                                    <span id="balanceStatus" class="badge bg-danger me-2">Unbalanced</span>
                                    <small id="balanceDifference" class="text-muted">Difference: 0.00৳</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Memo/Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                name="description" rows="2"
                                placeholder="Enter journal entry description...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Journal Entries</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEntryRow()">
                                    <i class="fas fa-plus me-1"></i>Add Line
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="entriesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Chart of Account</th>
                                            <th>Financial Account</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Memo</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="entriesTableBody">
                                        <!-- Entry rows will be added here dynamically -->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="2" class="text-end">Totals:</th>
                                            <th id="totalDebit">0.00৳</th>
                                            <th id="totalCredit">0.00৳</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveJournalBtn">
                            <i class="fas fa-save me-2"></i>Save Journal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteJournalModal" tabindex="-1" aria-labelledby="deleteJournalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title" id="deleteJournalModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-trash-alt fa-3x text-danger opacity-50"></i>
                    </div>
                    <p class="text-center mb-0">Are you sure you want to delete the journal entry:</p>
                    <p class="text-center fw-bold fs-5 text-dark mb-3" id="deleteJournalVoucherNo"></p>
                    <p class="text-center text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                        <i class="fas fa-trash-alt me-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let entryRowCount = 0;
            const chartAccounts = @json($chartAccounts ?? []);
            const financialAccounts = @json($financialAccounts ?? []);
            let deleteJournalId = null;

            // Define deleteJournal function in global scope so onclick can access it
            function deleteJournal(id, voucherNo) {
                // Store the journal ID for later use
                deleteJournalId = id;
                
                // Set the voucher number in the modal
                $('#deleteJournalVoucherNo').text(voucherNo);
                
                // Show the custom modal using Bootstrap 5 API
                const modalElement = document.getElementById('deleteJournalModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }

            $(document).ready(function () {
                // Initialize with at least 2 entry rows
                addEntryRow();
                addEntryRow();

                // Handle delete confirmation button click
                $('#confirmDeleteBtn').on('click', function() {
                    if (deleteJournalId === null) return;

                    // Disable the button and show loading state
                    const $btn = $(this);
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');

                    $.ajax({
                        url: '{{ route("journal.destroy", ":id") }}'.replace(':id', deleteJournalId),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                // Hide modal
                                $('#deleteJournalModal').modal('hide');
                                
                                // Show success message (you can use a toast notification here)
                                location.reload();
                            } else {
                                alert('Error: ' + response.message);
                                $btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i>Delete');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                            alert('An error occurred while deleting the journal entry.');
                            $btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i>Delete');
                        }
                    });
                });

                // Reset the delete button when modal is hidden
                $('#deleteJournalModal').on('hidden.bs.modal', function() {
                    deleteJournalId = null;
                    $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i>Delete');
                });

                // Handle delete button clicks using event delegation
                $(document).on('click', '.delete-journal-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const journalId = $(this).data('journal-id');
                    const voucherNo = $(this).data('voucher-no');
                    
                    console.log('Delete button clicked via event delegation!', journalId, voucherNo);
                    
                    // Call the delete function
                    deleteJournal(journalId, voucherNo);
                });

                // Handle form submission
                $('#journalForm').on('submit', function () {
                    // Set default values for empty debit/credit fields
                    $('.debit-input').each(function () {
                        if ($(this).val() === '') {
                            $(this).val('0');
                        }
                    });
                    $('.credit-input').each(function () {
                        if ($(this).val() === '') {
                            $(this).val('0');
                        }
                    });

                    var $submitBtn = $(this).find('button[type="submit"]');
                    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
                });

                // Modal reset on close
                $('#addJournalModal').on('hidden.bs.modal', function () {
                    var $form = $(this).find('form');
                    $form[0].reset();
                    $form.find('.is-invalid').removeClass('is-invalid');

                    // Reset entries table
                    $('#entriesTableBody').empty();
                    entryRowCount = 0;
                    addEntryRow();
                    addEntryRow();

                    // Reset form action and method for create operations
                    $form.attr('action', '{{ route("journal.store") }}');
                    $form.find('input[name="_method"]').remove();

                    // Reset modal title
                    $('#addJournalModalLabel').text('New Journal Entry');

                    // Reset balance status
                    updateBalanceStatus();
                });
            });

            function addEntryRow() {
                entryRowCount++;
                const rowHtml = `
                        <tr id="entryRow${entryRowCount}">
                            <td>
                                <select class="form-control chart-account-select" name="entries[${entryRowCount}][chart_of_account_id]" required>
                                    <option value="">Select Account</option>
                                    ${chartAccounts.map(account =>
                    `<option value="${account.id}">${account.name} (${account.code}) - ${account.parent ? account.parent.name : 'N/A'}</option>`
                ).join('')}
                                </select>
                            </td>
                            <td>
                                <select class="form-control financial-account-select" name="entries[${entryRowCount}][financial_account_id]">
                                    <option value="">Select Financial Account</option>
                                    ${financialAccounts.map(account =>
                    `<option value="${account.id}">${account.provider_name} - ${account.account_number}</option>`
                ).join('')}
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control debit-input" name="entries[${entryRowCount}][debit]" 
                                       step="0.01" min="0" placeholder="0.00" onchange="updateTotals()">
                            </td>
                            <td>
                                <input type="number" class="form-control credit-input" name="entries[${entryRowCount}][credit]" 
                                       step="0.01" min="0" placeholder="0.00" onchange="updateTotals()">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="entries[${entryRowCount}][memo]" 
                                       placeholder="Memo">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEntryRow(${entryRowCount})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                $('#entriesTableBody').append(rowHtml);
            }

            function removeEntryRow(rowId) {
                $(`#entryRow${rowId}`).remove();
                updateTotals();
            }

            function updateTotals() {
                let totalDebit = 0;
                let totalCredit = 0;

                $('.debit-input').each(function () {
                    totalDebit += parseFloat($(this).val()) || 0;
                });

                $('.credit-input').each(function () {
                    totalCredit += parseFloat($(this).val()) || 0;
                });

                $('#totalDebit').text(totalDebit.toFixed(2) + '৳');
                $('#totalCredit').text(totalCredit.toFixed(2) + '৳');

                updateBalanceStatus(totalDebit, totalCredit);
            }

            function updateBalanceStatus(totalDebit = 0, totalCredit = 0) {
                if (totalDebit === 0 && totalCredit === 0) {
                    // Calculate from current inputs
                    $('.debit-input').each(function () {
                        totalDebit += parseFloat($(this).val()) || 0;
                    });
                    $('.credit-input').each(function () {
                        totalCredit += parseFloat($(this).val()) || 0;
                    });
                }

                const difference = Math.abs(totalDebit - totalCredit);
                const isBalanced = difference < 0.01;

                if (isBalanced) {
                    $('#balanceStatus').removeClass('bg-danger').addClass('bg-success').text('Balanced');
                    // $('#saveJournalBtn').prop('disabled', false);
                } else {
                    $('#balanceStatus').removeClass('bg-success').addClass('bg-danger').text('Unbalanced');
                    // $('#saveJournalBtn').prop('disabled', true);
                }

                $('#balanceDifference').text('Difference: ' + difference.toFixed(2) + '৳');
            }

            function editJournal(id, date, description, type) {
                // Clear existing entries
                $('#entriesTableBody').empty();
                entryRowCount = 0;

                // Populate form fields
                $('#entry_date').val(date);
                $('#description').val(description);
                $('#type').val(type);

                // Set the form action and method for update
                $('#journalForm').attr('action', '{{ url("erp/journal") }}/' + id);
                $('#journalForm').find('input[name="_method"]').remove();
                $('#journalForm').append('<input type="hidden" name="_method" value="PUT">');

                // Update modal title
                $('#addJournalModalLabel').text('Edit Journal Entry');

                // Show the modal first
                $('#addJournalModal').modal('show');

                // Fetch journal entries via AJAX
                $.ajax({
                    url: '{{ route("journal.show", ":id") }}'.replace(':id', id) + '/entries',
                    type: 'GET',
                    success: function (response) {
                        if (response.entries && response.entries.length > 0) {
                            response.entries.forEach(function (entry) {
                                entryRowCount++;

                                // Create chart account options
                                let chartAccountOptions = '<option value="">Select Account</option>';
                                chartAccounts.forEach(function (account) {
                                    const selected = account.id == entry.chart_of_account_id ? 'selected' : '';
                                    const parentName = account.parent ? account.parent.name : 'N/A';
                                    chartAccountOptions += '<option value="' + account.id + '" ' + selected + '>' +
                                        account.name + ' (' + account.code + ') - ' + parentName + '</option>';
                                });

                                // Create financial account options
                                let financialAccountOptions = '<option value="">Select Financial Account</option>';
                                financialAccounts.forEach(function (account) {
                                    const selected = account.id == entry.financial_account_id ? 'selected' : '';
                                    financialAccountOptions += '<option value="' + account.id + '" ' + selected + '>' +
                                        account.provider_name + ' - ' + account.account_number + '</option>';
                                });

                                const rowHtml = '<tr id="entryRow' + entryRowCount + '">' +
                                    '<td>' +
                                    '<select class="form-control chart-account-select" name="entries[' + entryRowCount + '][chart_of_account_id]" required>' +
                                    chartAccountOptions +
                                    '</select>' +
                                    '</td>' +
                                    '<td>' +
                                    '<select class="form-control financial-account-select" name="entries[' + entryRowCount + '][financial_account_id]">' +
                                    financialAccountOptions +
                                    '</select>' +
                                    '</td>' +
                                    '<td>' +
                                    '<input type="number" class="form-control debit-input" name="entries[' + entryRowCount + '][debit]" ' +
                                    'step="0.01" min="0" placeholder="0.00" value="' + (entry.debit || 0) + '" onchange="updateTotals()">' +
                                    '</td>' +
                                    '<td>' +
                                    '<input type="number" class="form-control credit-input" name="entries[' + entryRowCount + '][credit]" ' +
                                    'step="0.01" min="0" placeholder="0.00" value="' + (entry.credit || 0) + '" onchange="updateTotals()">' +
                                    '</td>' +
                                    '<td>' +
                                    '<input type="text" class="form-control" name="entries[' + entryRowCount + '][memo]" ' +
                                    'placeholder="Memo" value="' + (entry.memo || '') + '">' +
                                    '</td>' +
                                    '<td>' +
                                    '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEntryRow(' + entryRowCount + ')">' +
                                    '<i class="fas fa-trash"></i>' +
                                    '</button>' +
                                    '</td>' +
                                    '</tr>';

                                $('#entriesTableBody').append(rowHtml);
                            });
                        } else {
                            // Add at least 2 empty rows if no entries
                            addEntryRow();
                            addEntryRow();
                        }

                        // Update totals and balance status
                        updateTotals();
                    },
                    error: function () {
                        // Add default rows if AJAX fails
                        addEntryRow();
                        addEntryRow();
                        updateTotals();
                    }
                });
            }
        </script>
    @endpush
@endsection