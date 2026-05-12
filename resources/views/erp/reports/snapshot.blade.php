@extends('erp.master')
@section('title', 'Business Snapshot')

@section('body')
@include('erp.components.sidebar')
@include('erp.components.header')

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="row mb-3 align-items-center">
            <div class="col-md-4">
                <h3 class="mb-0">Business Snapshot <span class="badge bg-primary fs-6">Daily Monitor</span></h3>
                <p class="text-muted mb-0">Quick overview of financial activities</p>
            </div>
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-body p-3">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1"><i class="fas fa-calendar-alt me-1"></i> Start Date</label>
                        <input type="date" name="start_date" class="form-control border-light-subtle rounded-3" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1"><i class="fas fa-calendar-alt me-1"></i> End Date</label>
                        <input type="date" name="end_date" class="form-control border-light-subtle rounded-3" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    @if(!$restrictedBranchId)
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1"><i class="fas fa-store me-1"></i> Select Branch</label>
                        <select name="branch_id" class="form-select select2" data-placeholder="All Branches">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3 d-flex gap-2">
                        <button type="button" class="btn btn-primary px-4 rounded-3 w-100 fw-bold" onclick="fetchData()">
                            <i class="fas fa-filter me-1"></i> Filter Activity
                        </button>
                        <button type="button" class="btn btn-light border px-3 rounded-3" onclick="resetFilters()" title="Reset">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

<div id="snapshot-content">
    @include('erp.reports.snapshot-partial', ['data' => $data])
</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fetchData() {
        const formData = $('#filterForm').serialize();
        $('#snapshot-content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Updating snapshot...</p></div>');
        
        $.ajax({
            url: "{{ route('reports.snapshot') }}",
            type: "GET",
            data: formData,
            success: function(response) {
                $('#snapshot-content').html(response);
            },
            error: function() {
                if(typeof toastr !== 'undefined') {
                    toastr.error('Failed to load data. Please try again.');
                }
                $('#snapshot-content').html('<div class="alert alert-danger">Error loading data.</div>');
            }
        });
    }

    function resetFilters() {
        $('input[type="date"]').val('');
        $('select[name="branch_id"]').val('').trigger('change.select2');
        fetchData();
    }
</script>
@endpush
