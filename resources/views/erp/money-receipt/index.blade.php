@extends('erp.master')

@section('title', 'Money Receipt List')

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
                            <li class="breadcrumb-item active text-primary fw-600">Money Receipt</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Money Receipt Registry</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button class="btn btn-light border fw-bold shadow-sm" onclick="window.print()">
                        <i class="fas fa-print me-2 text-primary"></i>Print Report
                    </button>
                    <a href="{{ route('money-receipt.create') }}" class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-plus-circle me-2"></i>New Receipt
                    </a>
                </div>
            </div>
        </div>
        
        <div class="container-fluid px-4 py-4">
            
            {{-- Alert --}}
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="alert-icon-me-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="alert-heading mb-1 fw-bold">Success!</h6>
                            <p class="mb-0 small text-dark">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Advanced Filters -->
            <div class="premium-card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom p-4">
                     <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Refine Registry View</h6>
                        <!-- Report Period Toggles -->
                        <div class="d-flex gap-3 bg-light p-1 rounded-3">
                             <div class="form-check form-check-inline m-0 px-3 py-1 rounded-2 cursor-pointer transition-all">
                                <input class="form-check-input filter-input cursor-pointer" type="radio" name="report_type" id="daily" value="daily" {{ request('report_type') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-600 small text-muted cursor-pointer" for="daily">Daily</label>
                            </div>
                            <div class="form-check form-check-inline m-0 px-3 py-1 rounded-2 cursor-pointer transition-all">
                                <input class="form-check-input filter-input cursor-pointer" type="radio" name="report_type" id="monthly" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-600 small text-muted cursor-pointer" for="monthly">Monthly</label>
                            </div>
                            <div class="form-check form-check-inline m-0 px-3 py-1 rounded-2 cursor-pointer transition-all">
                                <input class="form-check-input filter-input cursor-pointer" type="radio" name="report_type" id="yearly" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-600 small text-muted cursor-pointer" for="yearly">Yearly</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                     <form id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2 daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">From Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2 daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">To Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            
                            <div class="col-md-2 monthly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 monthly-group yearly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Customer</label>
                                <select name="customer_id" class="form-select select2-simple">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Outlet</label>
                                <select name="branch_id" class="form-select select2-simple">
                                    <option value="">All Outlets</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div class="col-md-1">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">All</option>
                                    <option value="Cash" {{ request('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Bank" {{ request('payment_method') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                    <option value="Mobile Money" {{ request('payment_method') == 'Mobile Money' ? 'selected' : '' }}>MFS</option>
                                </select>
                            </div>
                             <div class="col-md-2">
                                 <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Receipt</label>
                                 <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted small"></i></span>
                                    <input type="text" name="search" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Receipt No..." value="{{ request('search') }}">
                                 </div>
                             </div>
                             <div class="col-md-1 d-flex gap-2">
                                 <a href="{{ route('money-receipt.index') }}" class="btn btn-light border flex-fill" title="Reset Filters" style="height: 41px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-sync-alt text-muted"></i>
                                 </a>
                                 <button type="button" class="btn btn-indigo-premium flex-fill" onclick="fetchData()" style="height: 41px;">
                                     <i class="fas fa-filter"></i>
                                 </button>
                             </div>
                        </div>
                     </form>
                </div>
            </div>

            <!-- Table -->
             <div class="premium-card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Receipt No.</th>
                                    <th>Receipt Date</th>
                                    <th>Inv. Date</th>
                                    <th>Customer</th>
                                    <th>Outlet</th>
                                    <th>Sales Invoice</th>
                                    <th class="text-end">Due</th>
                                    <th class="text-end">Paid</th>
                                    <th>Account</th>
                                    <th>Collector</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @include('erp.money-receipt.table_rows', ['receipts' => $receipts])
                            </tbody>
                            <tfoot class="bg-indigo-50 fw-bold">
                                <tr>
                                    <td colspan="8" class="text-end text-uppercase small py-3">Grand Total Paid</td>
                                    <td class="text-end text-success py-3" id="totalAmount">৳ {{ number_format($totalAmount, 2) }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                     <div class="card-footer bg-white py-3 border-top-0" id="paginationLinks">
                        {{ $receipts->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
             </div>
        </div>
    </div>

    <style>
        .btn-indigo-premium { background: #4e73df; color: white; border: none; }
        .btn-indigo-premium:hover { background: #2e59d9; color: white; }
        .bg-indigo-50 { background-color: #f8faff !important; }
        .premium-table thead th { background-color: #2d5a4c !important; color: white !important; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; height: 50px; vertical-align: middle; }
    </style>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-simple').select2({
                width: '100%'
            });

            // Toggle date groups based on report type
            $('input[name="report_type"]').on('change', function() {
                toggleDateGroups();
                fetchData(); // Auto-fetch when type changes
            });

            function toggleDateGroups() {
                const type = $('input[name="report_type"]:checked').val();
                $('.daily-group, .monthly-group, .yearly-group').hide();
                
                if (type === 'daily') {
                    $('.daily-group').show();
                } else if (type === 'monthly') {
                    $('.monthly-group').show();
                    $('.yearly-group').show(); 
                } else if (type === 'yearly') {
                    $('.yearly-group').show();
                }
            }

            toggleDateGroups();
        });
    </script>
    @endpush

    <script>
        // Debounce function for search
        let timeout = null;
        document.getElementById('searchInput').addEventListener('keyup', function (e) {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                fetchData();
            }, 500);
        });

        function fetchData(page = 1) {
            const form = document.getElementById('filterForm');
            const data = new FormData(form); 
            
            const reportType = document.querySelector('input[name="report_type"]:checked');
            if(reportType) data.append('report_type', reportType.value);
            
            data.append('page', page);

            const params = new URLSearchParams();
            for (const [key, value] of data.entries()) {
                params.append(key, value);
            }

            fetch(`{{ route('money-receipt.index') }}?` + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('tableBody').innerHTML = data.html;
                document.getElementById('totalAmount').innerText = data.totalAmount;
                document.getElementById('paginationLinks').innerHTML = data.pagination;
                
                // Re-bind pagination links
                bindPagination();
            })
            .catch(error => console.error('Error:', error));
        }

        function bindPagination() {
            document.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    let url = this.getAttribute('href');
                    const urlParams = new URLSearchParams(url.split('?')[1]);
                    let page = urlParams.get('page');
                    fetchData(page);
                });
            });
        }
        
        // Initial binding
        bindPagination();
    </script>
@endsection
