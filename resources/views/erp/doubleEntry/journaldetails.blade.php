@extends('erp.master')

@section('title', 'Journal Details')

@push('css')
<style>
    .btn-action-premium {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #eef0f7;
        background: #fff;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .btn-action-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        border-color: currentColor;
    }
    .icon-box-sm {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ls-1 { letter-spacing: 0.5px; }
    .fw-500 { font-weight: 500; }
    .fw-600 { font-weight: 600; }
    .fw-700 { font-weight: 700; }
    
    @media print {
        .glass-header, .btn-create-premium, .btn-action-premium, .modal, .sidebar-wrapper, .main-header {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .premium-card {
            box-shadow: none !important;
            border: 1px solid #eee !important;
        }
    }
</style>
@endpush

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
                            <li class="breadcrumb-item"><a href="{{ route('journal.list') }}" class="text-decoration-none text-muted">Journal Entries</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Voucher Details</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Voucher #{{ $journal->voucher_no }}</h4>
                        @if($journal->isBalanced())
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                                <i class="fas fa-check-circle me-1"></i>Balanced
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1">
                                <i class="fas fa-exclamation-circle me-1"></i>Unbalanced
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button class="btn btn-light border shadow-sm fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-create-premium text-nowrap" onclick="exportJournal()">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                        <form action="{{ route('journal.destroy', $journal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this entire journal entry?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger text-white border-0" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Information & Summary Section -->
        <div class="container-fluid px-4 py-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="premium-card shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                <span class="icon-box-sm bg-primary bg-opacity-10 text-primary me-2 rounded">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                General Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Voucher No</label>
                                    <div class="h6 mb-0 fw-700 text-dark">{{ $journal->voucher_no ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Journal Date</label>
                                    <div class="h6 mb-0 fw-700 text-dark">{{ $journal->entry_date ? $journal->entry_date->format('d M, Y') : 'N/A' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Journal Type</label>
                                    <div>
                                        @if($journal->type)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1">{{ $journal->type }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border px-3 py-1">General</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Created By</label>
                                    <div class="h6 mb-0 fw-700 text-dark">{{ $journal->createdBy->first_name ?? ($journal->creator->first_name ?? 'N/A') }}</div>
                                </div>
                            </div>

                            @if($journal->description)
                            <div class="mt-4 pt-4 border-top">
                                <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Memo / Description</label>
                                <p class="mb-0 text-dark opacity-75 fw-500">{{ $journal->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="premium-card shadow-sm h-100 border-0 overflow-hidden position-relative bg-white">
                        <div class="card-body p-4 z-index-1 position-relative d-flex flex-column h-100">
                            <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">Voucher Financials</h5>
                            
                            <div class="flex-grow-1">
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-500">Total Debits</span>
                                    <span class="h5 mb-0 fw-bold text-success">৳{{ number_format($journal->total_debit, 2) }}</span>
                                </div>
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-500">Total Credits</span>
                                    <span class="h5 mb-0 fw-bold text-warning">৳{{ number_format($journal->total_credit, 2) }}</span>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Balanced Status</span>
                                @if($journal->isBalanced())
                                    <div class="d-flex align-items-center text-success fw-bold">
                                        <i class="fas fa-check-circle me-2 fa-lg"></i>
                                        <span>Yes, Balanced</span>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center text-danger fw-bold">
                                        <i class="fas fa-times-circle me-2 fa-lg"></i>
                                        <span>Unbalanced</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- Subtle background icon -->
                        <div class="position-absolute" style="bottom: -15px; right: -15px; opacity: 0.03; pointer-events: none;">
                            <i class="fas fa-balance-scale fa-10x text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Entries Section -->
        <div class="container-fluid px-4 pb-4">
            <div class="premium-card shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-4">
                     <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <span class="icon-box-sm bg-primary bg-opacity-10 text-primary me-2 rounded">
                            <i class="fas fa-list-ul"></i>
                        </span>
                        Journal Entries
                    </h5>
                    <button type="button" class="btn btn-create-premium px-4" onclick="showAddEntryModal()">
                        <i class="fas fa-plus me-2"></i>Add Line Entry
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Chart of Account</th>
                                    <th>Financial Account</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th>Memo</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="entriesTableBody">
                                @forelse($journal->entries as $entry)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $entry->chartOfAccount->name ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $entry->chartOfAccount->code ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($entry->financialAccount)
                                                <div class="text-dark">{{ $entry->financialAccount->provider_name ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $entry->financialAccount->account_number ?? 'N/A' }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($entry->debit > 0)
                                                <span class="fw-bold text-success">৳{{ number_format($entry->debit, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($entry->credit > 0)
                                                <span class="fw-bold text-warning">৳{{ number_format($entry->credit, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $entry->memo ?? '—' }}</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn-action-premium text-info" 
                                                        onclick="editEntry({{ $entry->id }})" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn-action-premium text-danger" 
                                                        onclick="deleteEntry({{ $entry->id }})" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <div class="opacity-25 mb-3">
                                                <i class="fas fa-receipt fa-4x"></i>
                                            </div>
                                            <h6 class="fw-bold">No transactions recorded yet</h6>
                                            <p class="small">Start by adding a debit or credit line to this voucher.</p>
                                            <button onclick="showAddEntryModal()" class="btn btn-primary btn-sm px-4 rounded-pill mt-2">
                                                <i class="fas fa-plus me-1"></i>Add First Line
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light bg-opacity-75">
                                <tr class="fw-bold border-top-0">
                                    <td colspan="2" class="text-end ps-4 py-4 text-dark h6 mb-0">Closing Totals</td>
                                    <td class="text-end text-success py-4 h6 mb-0">৳{{ number_format($journal->total_debit, 2) }}</td>
                                    <td class="text-end text-warning py-4 h6 mb-0">৳{{ number_format($journal->total_credit, 2) }}</td>
                                    <td colspan="2" class="pe-4 text-end">
                                        @if($journal->isBalanced())
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2 rounded-pill">
                                                <i class="fas fa-shield-alt me-2"></i>Voucher Balanced
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-4 py-2 rounded-pill">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Unbalanced
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Add Entry Modal -->
    <div class="modal fade" id="addEntryModal" tabindex="-1" aria-labelledby="addEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white p-4">
                    <h5 class="modal-title fw-bold" id="addEntryModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>New Transaction Line
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="entryForm" action="{{ route('journal.entry.store', $journal->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Chart of Account <span class="text-danger">*</span></label>
                                <select class="form-select border-2 @error('chart_of_account_id') is-invalid @enderror" 
                                        name="chart_of_account_id" required>
                                    <option value="">Select Account</option>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('chart_of_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Financial Account (Optional)</label>
                                <select class="form-select border-2" name="financial_account_id">
                                    <option value="">Select Financial Account</option>
                                    @foreach($financialAccounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->provider_name }} - {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success small">Debit Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success bg-opacity-10 text-success">৳</span>
                                    <input type="number" class="form-control border-2" id="debit" name="debit" 
                                           step="0.01" min="0" placeholder="0.00" oninput="validateAmounts()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-warning small">Credit Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-warning bg-opacity-10 text-warning">৳</span>
                                    <input type="number" class="form-control border-2" id="credit" name="credit" 
                                           step="0.01" min="0" placeholder="0.00" oninput="validateAmounts()">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Line Memo / Reference</label>
                                <textarea class="form-control border-2" name="memo" rows="2" placeholder="Describe this transaction line..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-4">
                        <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm" id="saveEntryBtn" disabled>
                            <i class="fas fa-save me-2"></i>Record Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modern Edit Entry Modal -->
    <div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white p-4">
                    <h5 class="modal-title fw-bold" id="editEntryModalLabel">
                        <i class="fas fa-edit me-2"></i>Modify Transaction Line
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editEntryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Chart of Account <span class="text-danger">*</span></label>
                                <select class="form-select border-2" id="edit_chart_of_account_id" name="chart_of_account_id" required>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Financial Account</label>
                                <select class="form-select border-2" id="edit_financial_account_id" name="financial_account_id">
                                    <option value="">Select Financial Account</option>
                                    @foreach($financialAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->provider_name }} - {{ $account->account_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success small">Debit Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success bg-opacity-10 text-success">৳</span>
                                    <input type="number" class="form-control border-2" id="edit_debit" name="debit" 
                                           step="0.01" min="0" placeholder="0.00" oninput="validateEditAmounts()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-warning small">Credit Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-warning bg-opacity-10 text-warning">৳</span>
                                    <input type="number" class="form-control border-2" id="edit_credit" name="credit" 
                                           step="0.01" min="0" placeholder="0.00" oninput="validateEditAmounts()">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Line Memo / Reference</label>
                                <textarea class="form-control border-2" id="edit_memo" name="memo" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-4">
                        <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white fw-bold px-4 shadow-sm" id="updateEntryBtn" disabled>
                            <i class="fas fa-check-circle me-2"></i>Update Line
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
    <script>
        function showAddEntryModal() {
            $('#addEntryModal').modal('show');
        }

        function validateAmounts() {
            const debit = parseFloat($('#debit').val()) || 0;
            const credit = parseFloat($('#credit').val()) || 0;
            const saveBtn = $('#saveEntryBtn');
            
            if (debit > 0 || credit > 0) {
                saveBtn.prop('disabled', false);
            } else {
                saveBtn.prop('disabled', true);
            }
        }

        function validateEditAmounts() {
            const debit = parseFloat($('#edit_debit').val()) || 0;
            const credit = parseFloat($('#edit_credit').val()) || 0;
            const updateBtn = $('#updateEntryBtn');
            
            if (debit > 0 || credit > 0) {
                updateBtn.prop('disabled', false);
            } else {
                updateBtn.prop('disabled', true);
            }
        }

        function editEntry(entryId) {
            // Fetch entry data via AJAX
            $.ajax({
                url: '{{ url("erp/journal-entry") }}/' + entryId,
                type: 'GET',
                success: function(response) {
                    const entry = response.entry;
                    
                    // Populate form fields
                    $('#edit_chart_of_account_id').val(entry.chart_of_account_id);
                    $('#edit_financial_account_id').val(entry.financial_account_id);
                    $('#edit_debit').val(entry.debit);
                    $('#edit_credit').val(entry.credit);
                    $('#edit_memo').val(entry.memo);
                    
                    // Set form action
                    $('#editEntryForm').attr('action', '{{ url("erp/journal-entry") }}/' + entryId);
                    
                    // Show modal
                    $('#editEntryModal').modal('show');
                    
                    // Validate amounts
                    validateEditAmounts();
                },
                error: function() {
                    alert('Error loading entry data');
                }
            });
        }

        function deleteEntry(entryId) {
            if (confirm('Are you sure you want to delete this entry?')) {
                $.ajax({
                    url: '{{ url("erp/journal-entry") }}/' + entryId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error deleting entry');
                        }
                    },
                    error: function() {
                        alert('Error deleting entry');
                    }
                });
            }
        }

        

        // Reset form when modal is closed
        $('#addEntryModal').on('hidden.bs.modal', function() {
            $('#entryForm')[0].reset();
            $('#saveEntryBtn').prop('disabled', true);
        });

        $('#editEntryModal').on('hidden.bs.modal', function() {
            $('#editEntryForm')[0].reset();
            $('#updateEntryBtn').prop('disabled', true);
        });
    </script>
@endsection