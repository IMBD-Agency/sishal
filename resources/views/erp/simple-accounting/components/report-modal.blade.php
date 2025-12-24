<!-- Sales Report Modal -->
<div class="modal fade" id="salesReportModal" tabindex="-1" aria-labelledby="salesReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="salesReportModalLabel">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Generate Sales Report
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reportFilterForm" class="row g-3 mb-4 p-3 bg-light rounded">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Report Type</label>
                        <select class="form-select form-select-sm" name="type" id="reportType">
                            <option value="product" selected>Product Wise</option>
                            <option value="category">Category Wise</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Sales Source</label>
                        <select class="form-select form-select-sm" name="source" id="reportSource">
                            <option value="all" selected>All Sales</option>
                            <option value="online">Online Only</option>
                            <option value="pos">POS Only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" id="reportDateFrom" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" id="reportDateTo" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="fetchReportData()">
                            <i class="fas fa-sync-alt me-1"></i> Update Preview
                        </button>
                    </div>
                </form>

                <!-- Summary Cards Preview -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white border-0 shadow-sm">
                            <div class="card-body py-3">
                                <h6 class="card-subtitle small opacity-75 mb-1">Total Revenue</h6>
                                <h4 class="card-title mb-0" id="summaryRevenue">0.00 TK</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white border-0 shadow-sm">
                            <div class="card-body py-3">
                                <h6 class="card-subtitle small opacity-75 mb-1">Total Profit</h6>
                                <h4 class="card-title mb-0" id="summaryProfit">0.00 TK</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-dark text-white border-0 shadow-sm">
                            <div class="card-body py-3">
                                <h6 class="card-subtitle small opacity-75 mb-1">Total Items Sold</h6>
                                <h4 class="card-title mb-0" id="summaryItems">0</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Preview -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover border">
                        <thead class="bg-light sticky-top">
                            <tr id="reportTableHeader">
                                <!-- Header will be populated by JS -->
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <!-- Body will be populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download me-1"></i> Export Report
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportReport('excel')">
                                <i class="fas fa-file-excel text-success me-2"></i> Export Excel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportReport('pdf')">
                                <i class="fas fa-file-pdf text-danger me-2"></i> Export PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('salesReportModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function () {
                fetchReportData();
            });
        }
    });

    function fetchReportData() {
        const type = document.getElementById('reportType').value;
        const source = document.getElementById('reportSource').value;
        const dateFrom = document.getElementById('reportDateFrom').value;
        const dateTo = document.getElementById('reportDateTo').value;
        
        const tbody = document.getElementById('reportTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading data...</td></tr>';

        $.ajax({
            url: "{{ route('simple-accounting.get-sales-report-data') }}",
            type: 'GET',
            data: { type, source, date_from: dateFrom, date_to: dateTo },
            success: function(response) {
                updateUI(response, type);
            },
            error: function(xhr) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error fetching data. Please try again.</td></tr>';
            }
        });
    }

    function updateUI(response, type) {
        // Update Headers
        const header = document.getElementById('reportTableHeader');
        if (type === 'category') {
            header.innerHTML = `
                <th>Category</th>
                <th class="text-end">Products</th>
                <th class="text-end">Qty Sold</th>
                <th class="text-end">Revenue</th>
                <th class="text-end">Profit</th>
            `;
        } else {
            header.innerHTML = `
                <th>Product</th>
                <th>Category</th>
                <th class="text-end">Qty Sold</th>
                <th class="text-end">Revenue</th>
                <th class="text-end">Profit</th>
            `;
        }

        // Update Body
        const tbody = document.getElementById('reportTableBody');
        tbody.innerHTML = '';
        
        if (response.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No data found for the selected range.</td></tr>`;
        } else {
            response.data.forEach(item => {
                const row = document.createElement('tr');
                if (type === 'category') {
                    row.innerHTML = `
                        <td>${item.name}</td>
                        <td class="text-end">${item.product_count}</td>
                        <td class="text-end">${item.quantity_sold}</td>
                        <td class="text-end">${item.revenue} TK</td>
                        <td class="text-end fw-bold text-success">${item.profit} TK</td>
                    `;
                } else {
                    row.innerHTML = `
                        <td><div class="fw-medium">${item.name}</div></td>
                        <td><span class="badge bg-light text-dark border">${item.category}</span></td>
                        <td class="text-end">${item.quantity_sold}</td>
                        <td class="text-end">${item.revenue} TK</td>
                        <td class="text-end fw-bold text-success">${item.profit} TK</td>
                    `;
                }
                tbody.appendChild(row);
            });
        }

        // Update Summary
        document.getElementById('summaryRevenue').innerText = response.summary.total_revenue + ' TK';
        document.getElementById('summaryProfit').innerText = response.summary.total_profit + ' TK';
        document.getElementById('summaryItems').innerText = response.summary.total_items;
    }

    function exportReport(format) {
        const type = document.getElementById('reportType').value;
        const source = document.getElementById('reportSource').value;
        const dateFrom = document.getElementById('reportDateFrom').value;
        const dateTo = document.getElementById('reportDateTo').value;
        
        let url = format === 'excel' 
            ? "{{ route('simple-accounting.export-excel') }}" 
            : "{{ route('simple-accounting.export-pdf') }}";
            
        const params = new URLSearchParams({
            type: type,
            source: source,
            date_from: dateFrom,
            date_to: dateTo
        });
        
        window.location.href = `${url}?${params.toString()}`;
    }
</script>
@endpush
