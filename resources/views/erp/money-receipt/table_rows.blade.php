@foreach($receipts as $index => $receipt)
<tr>
    <td class="text-center text-muted">{{ $receipts->firstItem() + $index }}</td>
    <td class="fw-bold text-primary">{{ $receipt->payment_reference ?? '-' }}</td>
    <td>{{ $receipt->payment_date }}</td>
    <td>{{ $receipt->invoice ? $receipt->invoice->issue_date : '-' }}</td>
    <td>{{ $receipt->customer ? $receipt->customer->name : '-' }}</td>
    <td>{{ $receipt->pos && $receipt->pos->branch ? $receipt->pos->branch->name : 'Main' }}</td>
    <td>{{ $receipt->invoice ? $receipt->invoice->invoice_number : '-' }}</td>
    <td class="text-end text-danger">{{ $receipt->invoice ? number_format($receipt->invoice->due_amount, 2) : '-' }}</td>
    <td class="text-end text-success fw-bold">{{ number_format($receipt->amount, 2) }}</td>
    <td>{{ $receipt->account_id ?? 'Cash' }}</td>
    <td>{{ $receipt->creator ? $receipt->creator->name : 'System' }}</td>
    <td class="text-center">
        <div class="d-flex justify-content-center gap-1">
            <!-- View Button -->
            <button class="btn btn-action btn-sm" title="View"><i class="fas fa-eye"></i></button>
            
            <!-- Delete Button -->
            <form action="{{ route('money-receipt.destroy', $receipt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this receipt? This will reverse the payment from the invoice.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-action-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
@endforeach
