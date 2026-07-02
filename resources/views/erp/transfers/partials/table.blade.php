                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>From (Source)</th>
                                    <th>To (Destination)</th>
                                    <th class="text-end">Amount</th>
                                    <th>Reference</th>
                                    <th>Memo</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $transfer)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $transfer->transfer_date->format('d M, Y') }}</div>
                                        <div class="small text-muted">{{ $transfer->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $transfer->fromAccount->provider_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            @if($transfer->fromAccount->branch_id)
                                                <span class="badge bg-soft-primary">{{ $transfer->fromAccount->branch->name ?? 'Branch' }}</span>
                                            @elseif($transfer->fromAccount->warehouse_id)
                                                <span class="badge bg-soft-info">{{ $transfer->fromAccount->warehouse->name ?? 'Warehouse' }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $transfer->toAccount->provider_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            @if($transfer->toAccount->branch_id)
                                                <span class="badge bg-soft-primary">{{ $transfer->toAccount->branch->name ?? 'Branch' }}</span>
                                            @elseif($transfer->toAccount->warehouse_id)
                                                <span class="badge bg-soft-info">{{ $transfer->toAccount->warehouse->name ?? 'Warehouse' }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary fs-5">{{ number_format($transfer->amount, 2) }}৳</span>
                                    </td>
                                    <td>{{ $transfer->reference ?: '-' }}</td>
                                    <td>{{ $transfer->memo ?: '-' }}</td>
                                    <td class="text-center pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                                                <li><a class="dropdown-item" href="{{ route('transfers.show', $transfer->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    @can('delete fund transfers')
                                                        <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST" onsubmit="return confirm('Delete this transfer? This will reverse the amounts.')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Delete</button>
                                                        </form>
                                                    @endcan
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted opacity-50">
                                            <i class="fas fa-exchange-alt fa-4x mb-3"></i>
                                            <p class="fw-bold mb-0">No fund transfers found.</p>
                                            <a href="{{ route('transfers.create') }}" class="btn btn-primary mt-3">Create First Transfer</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($transfers->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $transfers->links('vendor.pagination.bootstrap-5') }}
                </div>
                @endif
