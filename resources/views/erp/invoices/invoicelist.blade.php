@extends('erp.master')

@section('title', 'Invoice Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}"
                                    class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Invoice List</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Invoice List</h2>
                    <p class="text-muted mb-0">Manage invoice information, contacts, and transactions efficiently.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <a href="{{ route('invoice.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Add Invoice
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="fas fa-download me-2"></i>Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="container-fluid px-4 py-4">
            <div class="mb-3">
                <form method="GET" action="" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Search (Invoice #, Customer, Salesman)</label>
                        <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Invoice #, Customer, Salesman">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ $filters['issue_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ $filters['due_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select">
                            <option value="">All</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('invoice.list') }}" class="btn btn-outline-danger">Reset</a>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Invoice List</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="border-0">Invoice #</th>
                                    <th class="border-0">Order ID</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Salesman</th>
                                    <th class="border-0">Issue Date</th>
                                    <th class="border-0">Due Date</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Total</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td><a href="{{ route('invoice.show',$invoice->id) }}" class="btn btn-outline-primary">#{{ $invoice->invoice_number }}</a></td>
                                        <td>
                                            @if($invoice->order)
                                                <a href="{{ route('order.show', $invoice->order->id) }}" class="text-decoration-none">
                                                    #{{ $invoice->order->order_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->order->name ?? (optional($invoice->customer)->name ?? 'Walk-in-Customer') }}</td>
                                        <td>{{ trim((optional($invoice->salesman)->first_name ?? '') . ' ' . (optional($invoice->salesman)->last_name ?? '')) ?: 'System' }}</td>
                                        <td>{{ $invoice->issue_date }}</td>
                                        <td>{{ $invoice->due_date }}</td>
                                        <td>
                                            <span class="badge bg-secondary status-badge" 
                                                  data-id="{{ $invoice->id }}" 
                                                  data-status="{{ $invoice->status }}"
                                                  style="cursor:pointer;">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($invoice->total_amount, 2) }} ৳</td>
                                        <td>
                                            {{-- <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-info btn-sm">View</a> --}}
                                            <a href="{{ route('invoice.edit', $invoice->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                            
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No invoices found for the given criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
                        </span>
                        {{ $invoices->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Modal -->
        <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalLabel">Invoice Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Report Filters -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Issue Date From</label>
                                <input type="date" class="form-control" id="issueDateFrom" name="issue_date_from">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Issue Date To</label>
                                <input type="date" class="form-control" id="issueDateTo" name="issue_date_to">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="statusFilter" name="status">
                                    <option value="">All Status</option>
                                    <option value="paid">Paid</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partial">Partial</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Customer</label>
                                <select class="form-select" id="customerFilter" name="customer_id">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Column Selection -->
                        <div class="mb-4">
                            <h6>Select Columns to Include:</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="invoice_number" id="col_invoice_number" checked>
                                        <label class="form-check-label" for="col_invoice_number">Invoice Number</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="order_id" id="col_order_id" checked>
                                        <label class="form-check-label" for="col_order_id">Order ID</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="customer" id="col_customer" checked>
                                        <label class="form-check-label" for="col_customer">Customer</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="salesman" id="col_salesman" checked>
                                        <label class="form-check-label" for="col_salesman">Salesman</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="issue_date" id="col_issue_date" checked>
                                        <label class="form-check-label" for="col_issue_date">Issue Date</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="due_date" id="col_due_date" checked>
                                        <label class="form-check-label" for="col_due_date">Due Date</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="status" id="col_status" checked>
                                        <label class="form-check-label" for="col_status">Status</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="subtotal" id="col_subtotal" checked>
                                        <label class="form-check-label" for="col_subtotal">Subtotal</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="tax" id="col_tax" checked>
                                        <label class="form-check-label" for="col_tax">Tax</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="discount" id="col_discount" checked>
                                        <label class="form-check-label" for="col_discount">Discount</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="total" id="col_total" checked>
                                        <label class="form-check-label" for="col_total">Total</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="paid_amount" id="col_paid_amount" checked>
                                        <label class="form-check-label" for="col_paid_amount">Paid Amount</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input column-selector" type="checkbox" value="due_amount" id="col_due_amount" checked>
                                        <label class="form-check-label" for="col_due_amount">Due Amount</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllColumns">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllColumns">Deselect All</button>
                            </div>
                        </div>

                        <!-- Summary Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Total Invoices</h5>
                                        <h3 id="totalInvoices">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Total Amount</h5>
                                        <h3 id="totalAmount">৳0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Paid</h5>
                                        <h3 id="paidInvoices">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Unpaid</h5>
                                        <h3 id="unpaidInvoices">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Partial</h5>
                                        <h3 id="partialInvoices">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Report Preview -->
                        <div class="mb-4">
                            <h6>Report Preview:</h6>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-bordered" id="reportPreviewTable">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="col-invoice-number">Invoice #</th>
                                            <th class="col-order-id">Order ID</th>
                                            <th class="col-customer">Customer</th>
                                            <th class="col-salesman">Salesman</th>
                                            <th class="col-issue-date">Issue Date</th>
                                            <th class="col-due-date">Due Date</th>
                                            <th class="col-status">Status</th>
                                            <th class="col-subtotal">Subtotal</th>
                                            <th class="col-tax">Tax</th>
                                            <th class="col-discount">Discount</th>
                                            <th class="col-total">Total</th>
                                            <th class="col-paid-amount">Paid</th>
                                            <th class="col-due-amount">Due</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reportPreviewBody">
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Export Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-success" id="exportExcel">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </button>
                            <button type="button" class="btn btn-danger" id="exportPdf">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('issueDateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
    document.getElementById('issueDateTo').value = today.toISOString().split('T')[0];

    // Load initial report data
    loadReportData();

    // Event listeners for filters
    document.getElementById('issueDateFrom').addEventListener('change', loadReportData);
    document.getElementById('issueDateTo').addEventListener('change', loadReportData);
    document.getElementById('statusFilter').addEventListener('change', loadReportData);
    document.getElementById('customerFilter').addEventListener('change', loadReportData);

    // Column selection
    document.getElementById('selectAllColumns').addEventListener('click', function() {
        document.querySelectorAll('.column-selector').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateColumnVisibility();
    });

    document.getElementById('deselectAllColumns').addEventListener('click', function() {
        document.querySelectorAll('.column-selector').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateColumnVisibility();
    });

    document.querySelectorAll('.column-selector').forEach(checkbox => {
        checkbox.addEventListener('change', updateColumnVisibility);
    });

    // Export buttons
    document.getElementById('exportExcel').addEventListener('click', exportToExcel);
    document.getElementById('exportPdf').addEventListener('click', exportToPdf);

    function loadReportData() {
        const issueDateFrom = document.getElementById('issueDateFrom').value;
        const issueDateTo = document.getElementById('issueDateTo').value;
        const status = document.getElementById('statusFilter').value;
        const customerId = document.getElementById('customerFilter').value;

        // Show loading
        document.getElementById('reportPreviewBody').innerHTML = '<tr><td colspan="13" class="text-center">Loading...</td></tr>';

        fetch(`/erp/invoices/report-data?issue_date_from=${issueDateFrom}&issue_date_to=${issueDateTo}&status=${status}&customer_id=${customerId}`)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}...`);
                    });
                }
                return response.json();
            })
            .then(data => {
                updateReportPreview(data.invoices);
                updateSummaryStats(data.summary);
            })
            .catch(error => {
                console.error('Error loading report data:', error);
                document.getElementById('reportPreviewBody').innerHTML = '<tr><td colspan="13" class="text-center text-danger">Error loading data: ' + error.message + '</td></tr>';
            });
    }

    function updateReportPreview(invoices) {
        const tbody = document.getElementById('reportPreviewBody');
        tbody.innerHTML = '';

        if (invoices.length === 0) {
            tbody.innerHTML = '<tr><td colspan="13" class="text-center">No data found</td></tr>';
            return;
        }

        invoices.forEach(invoice => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="col-invoice-number">${invoice.invoice_number || '-'}</td>
                <td class="col-order-id">${invoice.order_id || '-'}</td>
                <td class="col-customer">${invoice.customer_name || 'Walk-in Customer'}</td>
                <td class="col-salesman">${invoice.salesman_name || 'System'}</td>
                <td class="col-issue-date">${invoice.issue_date || '-'}</td>
                <td class="col-due-date">${invoice.due_date || '-'}</td>
                <td class="col-status"><span class="badge bg-${getStatusBadgeColor(invoice.status)}">${invoice.status || '-'}</span></td>
                <td class="col-subtotal">৳${invoice.subtotal || '0'}</td>
                <td class="col-tax">৳${invoice.tax || '0'}</td>
                <td class="col-discount">৳${invoice.discount || '0'}</td>
                <td class="col-total">৳${invoice.total_amount || '0'}</td>
                <td class="col-paid-amount">৳${invoice.paid_amount || '0'}</td>
                <td class="col-due-amount">৳${invoice.due_amount || '0'}</td>
            `;
            tbody.appendChild(row);
        });
    }

    function updateSummaryStats(summary) {
        document.getElementById('totalInvoices').textContent = summary.total_invoices || 0;
        document.getElementById('totalAmount').textContent = '৳' + (summary.total_amount || 0);
        document.getElementById('paidInvoices').textContent = summary.paid_invoices || 0;
        document.getElementById('unpaidInvoices').textContent = summary.unpaid_invoices || 0;
        document.getElementById('partialInvoices').textContent = summary.partial_invoices || 0;
    }

    function updateColumnVisibility() {
        const columns = {
            'invoice_number': 'col-invoice-number',
            'order_id': 'col-order-id',
            'customer': 'col-customer',
            'salesman': 'col-salesman',
            'issue_date': 'col-issue-date',
            'due_date': 'col-due-date',
            'status': 'col-status',
            'subtotal': 'col-subtotal',
            'tax': 'col-tax',
            'discount': 'col-discount',
            'total': 'col-total',
            'paid_amount': 'col-paid-amount',
            'due_amount': 'col-due-amount'
        };

        Object.keys(columns).forEach(key => {
            const checkbox = document.getElementById('col_' + key);
            const columnClass = columns[key];
            const elements = document.querySelectorAll('.' + columnClass);
            
            elements.forEach(element => {
                element.style.display = checkbox.checked ? '' : 'none';
            });
        });
    }

    function getStatusBadgeColor(status) {
        switch(status) {
            case 'paid': return 'success';
            case 'unpaid': return 'danger';
            case 'partial': return 'warning';
            default: return 'secondary';
        }
    }

    function exportToExcel() {
        const issueDateFrom = document.getElementById('issueDateFrom').value;
        const issueDateTo = document.getElementById('issueDateTo').value;
        const status = document.getElementById('statusFilter').value;
        const customerId = document.getElementById('customerFilter').value;
        const selectedColumns = Array.from(document.querySelectorAll('.column-selector:checked')).map(cb => cb.value);

        if (selectedColumns.length === 0) {
            alert('Please select at least one column to export.');
            return;
        }

        const url = `/erp/invoices/export-excel?issue_date_from=${issueDateFrom}&issue_date_to=${issueDateTo}&status=${status}&customer_id=${customerId}&columns=${selectedColumns.join(',')}`;
        
        // Show loading state
        const btn = document.getElementById('exportExcel');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Excel...';
        btn.disabled = true;
        
        // Use fetch to handle potential errors
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Export failed');
                    });
                }
                return response.blob();
            })
            .then(blob => {
                // Create download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'invoice_report_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.xlsx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                alert('Export failed: ' + error.message);
            })
            .finally(() => {
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }

    function exportToPdf() {
        const issueDateFrom = document.getElementById('issueDateFrom').value;
        const issueDateTo = document.getElementById('issueDateTo').value;
        const status = document.getElementById('statusFilter').value;
        const customerId = document.getElementById('customerFilter').value;
        const selectedColumns = Array.from(document.querySelectorAll('.column-selector:checked')).map(cb => cb.value);

        if (selectedColumns.length === 0) {
            alert('Please select at least one column to export.');
            return;
        }

        const url = `/erp/invoices/export-pdf?issue_date_from=${issueDateFrom}&issue_date_to=${issueDateTo}&status=${status}&customer_id=${customerId}&columns=${selectedColumns.join(',')}`;
        
        // Show loading state
        const btn = document.getElementById('exportPdf');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating PDF...';
        btn.disabled = true;
        
        // Use fetch to handle potential errors
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Export failed');
                    });
                }
                return response.blob();
            })
            .then(blob => {
                // Create download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'invoice_report_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                alert('Export failed: ' + error.message);
            })
            .finally(() => {
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }
});
</script>
@endpush