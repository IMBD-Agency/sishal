@extends('erp.master')

@section('title', 'Supplier Ledger - ' . $supplier->name)

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
                            <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none text-muted">Suppliers</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('suppliers.show', $supplier->id) }}" class="text-decoration-none text-muted">{{ $supplier->name }}</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Financial Ledger</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-warning text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-book"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Supplier Statement</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-3 align-items-md-center">
                    <div class="px-3 py-2 bg-danger-subtle border border-danger border-opacity-10 rounded-3 text-center">
                        <div class="extra-small text-danger fw-bold text-uppercase opacity-75">Payable Balance</div>
                        <div class="h5 fw-bold text-danger mb-0">{{ number_format($supplier->balance, 2) }}৳</div>
                    </div>
                    <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-create-premium">
                        <i class="fas fa-money-bill-wave me-2"></i>Record Payment
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="premium-card">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-list-ul me-2 text-primary"></i>Transaction History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Posting Date</th>
                                    <th>Transaction Details</th>
                                    <th>Reference</th>
                                    <th class="text-end">Debit (Out)</th>
                                    <th class="text-end">Credit (In)</th>
                                    <th class="text-end pe-4">Running Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                <tr>
                                    <td class="ps-4 small text-muted">{{ $entry->date->format('d M, Y') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark mb-1">{{ $entry->description }}</div>
                                        @if($entry->transactionable_type)
                                            <span class="extra-small text-muted text-uppercase tracking-wider">{{ class_basename($entry->transactionable_type) }} #{{ $entry->transactionable_id }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($entry->transactionable)
                                            @if($entry->transactionable_type == 'App\Models\PurchaseBill')
                                                <span class="badge bg-secondary rounded-pill px-3">BILL-{{ $entry->transactionable->bill_number ?: $entry->transactionable->id }}</span>
                                            @elseif($entry->transactionable_type == 'App\Models\SupplierPayment')
                                                <span class="badge bg-success rounded-pill px-3">PAY-{{ $entry->transactionable->id }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                        {{ $entry->debit > 0 ? number_format($entry->debit, 2).'৳' : '-' }}
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        {{ $entry->credit > 0 ? number_format($entry->credit, 2).'৳' : '-' }}
                                    </td>
                                    <td class="text-end pe-4 fw-bold {{ $entry->balance > 0 ? 'text-danger' : 'text-primary' }}">
                                        {{ number_format($entry->balance, 2) }}৳
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-book-open fa-3x mb-3 opacity-25"></i>
                                            <p class="fw-bold">No ledger activity recorded.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($entries->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Statement Records</small>
                        {{ $entries->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
