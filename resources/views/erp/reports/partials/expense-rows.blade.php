@forelse($expenses as $index => $expense)
    <tr>
        <td class="px-3 fw-bold text-muted">{{ $index + 1 }}</td>
        <td>{{ \Carbon\Carbon::parse($expense['date'])->format('d/m/Y') }}</td>
        <td><span class="badge bg-light text-dark border">{{ $expense['ref_no'] }}</span></td>
        <td class="fw-bold text-dark">{{ $expense['category'] }}</td>
        <td>{{ $expense['branch'] }}</td>
        <td class="text-muted small">{{ \Illuminate\Support\Str::limit($expense['note'], 30) }}</td>
        <td class="text-end fw-bold pe-3">{{ number_format($expense['amount'], 2) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted bg-light">
            No data available in table
        </td>
    </tr>
@endforelse
