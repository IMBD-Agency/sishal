@extends('erp.master')

@section('title', 'Expense Reports')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Expense Reports</h4>
                    <p class="text-muted small mb-0">Track and manage business expenditures</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <!-- Add Export buttons later if needed -->
                </div>
            </div>

            <!-- Enhanced Filter Card -->
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body p-4">
                    <form id="filterForm" method="GET" action="{{ route('reports.expenses') }}">
                        <!-- Radio Toggles -->
                        <div class="mb-3 d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input cursor-pointer" type="radio" name="report_type" id="daily" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }} onclick="setReportType('daily')">
                                <label class="form-check-label fw-bold cursor-pointer" for="daily">Daily Reports</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input cursor-pointer" type="radio" name="report_type" id="monthly" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }} onclick="setReportType('monthly')">
                                <label class="form-check-label fw-bold cursor-pointer" for="monthly">Monthly Reports</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input cursor-pointer" type="radio" name="report_type" id="yearly" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }} onclick="setReportType('yearly')">
                                <label class="form-check-label fw-bold cursor-pointer" for="yearly">Yearly Reports</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">End Date *</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate->toDateString() }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Select Expense Type *</label>
                                <select name="expense_category_id" class="form-select select2">
                                    <option value="">All Expenses</option>
                                    @foreach($expenseCategories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success text-white w-100 fw-bold">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Header -->
            <div class="text-center mb-4">
                <h5 class="fw-bold">Expense Reports in : <span id="dateRangeHeading">{{ $startDate->format('Y-m-d') }} - {{ $endDate->format('Y-m-d') }}</span></h5>
            </div>

            <!-- Action Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="btn-group">
                    <button class="btn btn-secondary btn-sm">CSV</button>
                    <button class="btn btn-secondary btn-sm">Excel</button>
                    <button class="btn btn-secondary btn-sm">PDF</button>
                    <button class="btn btn-secondary btn-sm" onclick="window.print()">Print</button>
                </div>
                <div class="d-flex align-items-center">
                    <label class="me-2 text-muted small">Search:</label>
                    <input type="text" class="form-control form-control-sm" style="width: 200px;" placeholder="Search expense...">
                </div>
            </div>

            <!-- Data Table -->
            <div class="card border shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-bordered">
                            <thead class="bg-success text-white table-dark">
                                <tr>
                                    <th class="py-3 px-3">#SN.</th>
                                    <th class="py-3">Date</th>
                                    <th class="py-3">Expense No.</th>
                                    <th class="py-3">Expense</th>
                                    <th class="py-3">Outlet</th>
                                    <th class="py-3">Notes</th>
                                    <th class="py-3 text-end pe-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="expenseTableBody">
                                @include('erp.reports.partials.expense-rows', ['expenses' => $expenses])
                            </tbody>
                            <tfoot class="bg-light fw-bold border-top">
                                <tr>
                                    <td colspan="6" class="text-end py-3 pe-3">Total Amount</td>
                                    <td class="text-end py-3 pe-3 fw-bold text-dark" id="totalAmount">{{ number_format($expenses->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .table-dark { background-color: #198754 !important; color: white; }
        .bg-success { background-color: #198754 !important; }
        .form-check-input:checked { background-color: #0d6efd; border-color: #0d6efd; }
    </style>

    <script>
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetchExpenses();
        });

        function fetchExpenses() {
            const form = document.getElementById('filterForm');
            const url = new URL(form.action);
            const params = new URLSearchParams(new FormData(form));
            // Add current time prevent cache
            params.append('_', new Date().getTime());

            // Build full URL
            const fullUrl = `${url}?${params.toString()}`;
            
            // Show loading state (optional: simple opacity change)
            document.getElementById('expenseTableBody').style.opacity = '0.5';

            fetch(fullUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('expenseTableBody').innerHTML = data.html;
                document.getElementById('totalAmount').innerText = data.total_amount;
                document.getElementById('dateRangeHeading').innerText = data.date_range;
                document.getElementById('expenseTableBody').style.opacity = '1';
            })
            .catch(error => {
                console.error('Error fetching expenses:', error);
                document.getElementById('expenseTableBody').style.opacity = '1';
                alert('Failed to load expenses. Please try again.');
            });
        }

        function setReportType(type) {
            let startDate = new Date();
            let endDate = new Date();
            let year = startDate.getFullYear();
            let month = startDate.getMonth(); // 0-11

            if (type === 'daily') {
                // Today
            } else if (type === 'monthly') {
                startDate = new Date(year, month, 1);
                endDate = new Date(year, month + 1, 0);
            } else if (type === 'yearly') {
                startDate = new Date(year, 0, 1);
                endDate = new Date(year, 11, 31);
            }

            const formatDate = (date) => {
                let d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            }

            document.getElementById('start_date').value = formatDate(startDate);
            document.getElementById('end_date').value = formatDate(endDate);
            
            // Trigger fetch instead of submit
            fetchExpenses();
        }

        // Live Search Filter
        document.querySelector('input[placeholder="Search expense..."]').addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            const rows = document.querySelectorAll('#expenseTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(value) > -1 ? '' : 'none';
            });
        });
    </script>
@endsection
