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
                    <a href="{{ route('requisition.create') }}" class="btn btn-create-premium px-4 shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>Create New Request
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 fw-bold animate__animated animate__fadeIn">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="premium-card">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small">
                        <i class="fas fa-list me-2 text-info"></i>Request History
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Req #</th>
                                    <th>Branch</th>
                                    <th>Target Warehouse</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requisitions as $req)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $req->requisition_number }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $req->branch->name }}</div>
                                            <div class="extra-small text-muted text-uppercase">{{ $req->branch->location }}</div>
                                        </td>
                                        <td>
                                            <div class="badge bg-light text-dark border fw-bold px-3 py-2">
                                                <i class="fas fa-warehouse me-1 text-info"></i>{{ $req->warehouse->name }}
                                            </div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($req->requisition_date)->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'pending' => 'bg-warning text-dark',
                                                    'partially_fulfilled' => 'bg-info text-white',
                                                    'fulfilled' => 'bg-success text-white',
                                                    'rejected' => 'bg-danger text-white',
                                                ][$req->status] ?? 'bg-secondary text-white';
                                            @endphp
                                            <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill text-uppercase" style="font-size: 0.7rem;">
                                                {{ str_replace('_', ' ', $req->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-bold">{{ $req->creator->name }}</div>
                                            <div class="extra-small text-muted">{{ $req->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('requisition.show', $req->id) }}" class="btn btn-sm btn-light border action-circle shadow-none" title="View Details">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </a>
                                                @if($req->status === 'pending')
                                                    <form action="{{ route('requisition.destroy', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this request?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light border action-circle shadow-none" title="Delete">
                                                            <i class="fas fa-trash text-danger"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p class="fw-bold mb-0">No requisitions found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top p-4">
                    {{ $requisitions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
