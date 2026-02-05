@extends('erp.master')

@section('title', 'Supplier Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Supplier Summary Report</h4>
                    <p class="text-muted small mb-0">Overview of purchases and supplier balances</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-4">Supplier Name</th>
                                    <th class="py-3">Contact</th>
                                    <th class="py-3 text-center">Contact Person</th>
                                    <th class="py-3 text-end">Total Purchased</th>
                                    <th class="py-3 text-end">Total Paid</th>
                                    <th class="py-3 text-end">Due Balance</th>
                                    <th class="py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <a href="{{ route('reports.supplier.ledger', $supplier->id) }}" class="text-decoration-none text-dark">
                                                {{ $supplier->name }}
                                            </a>
                                        </td>
                                        <td>{{ $supplier->phone }}</td>
                                        <td class="text-center">{{ $supplier->contact_person ?? '-' }}</td>
                                        <td class="text-end fw-bold">{{ number_format($supplier->total_purchase, 2) }}</td>
                                        <td class="text-end text-success">{{ number_format($supplier->total_paid, 2) }}</td>
                                        <td class="text-end fw-bold {{ $supplier->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($supplier->due_amount, 2) }} 
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('reports.supplier.ledger', $supplier->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-invoice me-1"></i> Ledger
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No suppliers found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
