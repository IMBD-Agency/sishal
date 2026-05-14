@extends('erp.master')

@section('title', 'Product Requisitions')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Requisitions</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-info text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Branch Requisitions</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    @can('manage requisitions')
                    <a href="{{ route('requisition.create') }}" class="btn btn-create-premium px-4 shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>Create New Request
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 fw-bold animate__animated animate__fadeIn">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <!-- Advanced Filter Card -->
            <div class="premium-card mb-4">
                <div class="card-body p-4">
                    <form id="filterForm" class="row g-3 align-items-end">
                        @if(!$restrictedBranchId)
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Branch</label>
                            <select name="branch_id" class="form-select border-0 bg-light shadow-none">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" class="form-select border-0 bg-light shadow-none">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="partially_fulfilled">Partially Fulfilled</option>
                                <option value="fulfilled">Fulfilled</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">From Date</label>
                            <input type="date" name="start_date" class="form-control border-0 bg-light shadow-none">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">To Date</label>
                            <input type="date" name="end_date" class="form-control border-0 bg-light shadow-none">
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="button" id="filterBtn" class="btn btn-primary flex-grow-1 shadow-sm fw-bold">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <button type="button" id="resetBtn" class="btn btn-light border">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button type="button" id="exportExcelBtn" class="btn btn-success border-0 shadow-sm" title="Export Excel">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                                <button type="button" id="exportPdfBtn" class="btn btn-danger border-0 shadow-sm" title="Export PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="input-group search-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text border-0 bg-light"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-0 bg-light ps-0" placeholder="Search by Requisition # (Press Enter to Filter)...">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="premium-card">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small">
                        <i class="fas fa-list me-2 text-info"></i>Request History
                    </h6>
                </div>
                <div class="card-body p-0" id="tableContainer">
                    @include('erp.requisition.partials.table')
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            /**
             * AJAX Pagination Logic:
             * When clicking pagination links, we prevent the default page reload and instead
             * call this function with the link's URL. The serialize() method ensures that 
             * all currently selected filters are sent along with the page number to the 
             * server, keeping the filtered state consistent across pages.
             */
            function fetchRequisitions(url = "{{ route('requisition.index') }}") {
                const formData = $('#filterForm').serialize();
                $('#tableContainer').css('opacity', '0.5');
                
                $.ajax({
                    url: url,
                    data: formData,
                    success: function(response) {
                        $('#tableContainer').html(response).css('opacity', '1');
                    }
                });
            }

            // Trigger filter only on button click or Enter key
            $('#filterBtn').on('click', function() {
                fetchRequisitions();
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                fetchRequisitions();
            });

            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                fetchRequisitions($(this).attr('href'));
            });

            $('#resetBtn').on('click', function() {
                $('#filterForm')[0].reset();
                fetchRequisitions();
            });

            /**
             * Export Logic & Progress Bar Fix:
             * Browsers trigger a 'beforeunload' event when changing window.location.href, 
             * which starts the top progress bar in master.blade.php. Since file downloads 
             * don't actually unload the page, the bar gets "stuck". 
             * We set isDownloadNavigation to true to tell the master template to ignore this transition.
             */
            function handleExport(route) {
                const formData = $('#filterForm').serialize();
                window.isDownloadNavigation = true; // Prevents stuck progress bar
                window.location.href = route + "?" + formData;
                
                // Reset flag after a delay to allow normal navigation later
                setTimeout(() => { window.isDownloadNavigation = false; }, 1000);
            }

            $('#exportExcelBtn').on('click', function() {
                handleExport("{{ route('requisition.export.excel') }}");
            });

            $('#exportPdfBtn').on('click', function() {
                handleExport("{{ route('requisition.export.pdf') }}");
            });
        });
    </script>

    </div>
@endsection
