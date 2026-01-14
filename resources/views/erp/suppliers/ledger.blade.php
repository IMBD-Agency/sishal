@extends('erp.master')

@section('title', 'Supplier Ledger - ' . $supplier->name)

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('suppliers.show', $supplier->id) }}">{{ $supplier->name }}</a></li>
                    <li class="breadcrumb-item active">Ledger</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Supplier Ledger</h4>
                            <p class="text-muted mb-0">{{ $supplier->name }} | {{ $supplier->company_name }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="p-3 bg-danger-subtle rounded-3 text-center" style="min-width: 150px;">
                                <div class="small text-danger fw-bold text-uppercase">Current Balance</div>
                                <div class="h4 fw-bold text-danger mb-0">tk {{ number_format($supplier->balance, 2) }}</div>
                            </div>
                            <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary d-flex align-items-center gap-2 px-4">
                                <i class="fas fa-money-bill-wave"></i> Make Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Description</th>
                                    <th>Reference</th>
                                    <th class="text-end">Debit (Payment)</th>
                                    <th class="text-end">Credit (Bill)</th>
                                    <th class="text-end pe-4">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                <tr>
                                    <td class="ps-4">{{ $entry->date->format('d M, Y') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $entry->description }}</div>
                                        @if($entry->transactionable_type)
                                            <small class="text-muted">{{ class_basename($entry->transactionable_type) }} #{{ $entry->transactionable_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($entry->transactionable)
                                            @if($entry->transactionable_type == 'App\Models\PurchaseBill')
                                                <span class="badge bg-info-subtle text-info">Bill #{{ $entry->transactionable->bill_number ?: $entry->transactionable->id }}</span>
                                            @elseif($entry->transactionable_type == 'App\Models\SupplierPayment')
                                                <span class="badge bg-success-subtle text-success">Pay #{{ $entry->transactionable->id }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                        {{ $entry->debit > 0 ? 'tk '.number_format($entry->debit, 2) : '-' }}
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        {{ $entry->credit > 0 ? 'tk '.number_format($entry->credit, 2) : '-' }}
                                    </td>
                                    <td class="text-end pe-4 fw-bold {{ $entry->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        tk {{ number_format($entry->balance, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-book-open fa-3x mb-3 opacity-25"></i>
                                        <p>No ledger entries found for this supplier.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($entries->hasPages())
                <div class="card-footer bg-transparent border-0 p-4">
                    {{ $entries->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
