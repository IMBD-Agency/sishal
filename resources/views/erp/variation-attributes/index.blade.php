@extends('erp.master')

@section('title', 'Variation Attributes')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            :root {
                --primary-indigo: #4f46e5;
                --primary-hover: #4338ca;
                --gray-50: #f9fafb;
                --gray-100: #f3f4f6;
                --gray-200: #e5e7eb;
                --gray-700: #374151;
            }

            .premium-card {
                background: #fff;
                border: 1px solid var(--gray-200);
                border-radius: 16px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                overflow: hidden;
            }

            .premium-table thead th {
                background: var(--gray-50);
                padding: 1rem 1.5rem;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--gray-700);
                border-bottom: 1px solid var(--gray-200);
            }

            .premium-table tbody td {
                padding: 1.25rem 1.5rem;
                vertical-align: middle;
                color: #111827;
                border-bottom: 1px solid var(--gray-100);
            }

            .value-chip {
                background: #f1f5f9;
                color: #475569;
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 0.75rem;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                border: 1px solid #e2e8f0;
            }

            .color-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                border: 1px solid rgba(0,0,0,0.1);
            }

            .btn-action {
                width: 32px;
                height: 32px;
                padding: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                transition: all 0.2s;
            }

            .status-badge {
                padding: 4px 12px;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .status-active { background: #dcfce7; color: #166534; }
            .status-inactive { background: #f1f5f9; color: #475569; }
        </style>

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Variation Attributes</h2>
                    <p class="text-muted mb-0">Manage product properties like Size, Color, and Material.</p>
                </div>
                <a href="{{ route('erp.variation-attributes.create') }}" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                    <i class="fas fa-plus me-2"></i>Add New Attribute
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="premium-card">
                <div class="table-responsive">
                    <table class="table premium-table mb-0">
                        <thead>
                            <tr>
                                <th>Attribute Name</th>
                                <th>Slug</th>
                                <th>Type</th>
                                <th>Sample Values</th>
                                <th>Required</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attributes as $attribute)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light p-2 rounded-3 me-3">
                                                <i class="fas fa-{{ $attribute->is_color ? 'palette' : 'tag' }} text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $attribute->name }}</div>
                                                @if($attribute->description)
                                                    <div class="text-muted small">{{ Str::limit($attribute->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><code class="text-indigo">{{ $attribute->slug }}</code></td>
                                    <td>
                                        @if($attribute->is_color)
                                            <span class="text-primary small fw-bold">Color-based</span>
                                        @else
                                            <span class="text-muted small fw-bold">Text-based</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($attribute->values->take(4) as $value)
                                                <div class="value-chip">
                                                    @if($attribute->is_color && $value->color_code)
                                                        <span class="color-dot" style="background-color: {{ $value->color_code }}"></span>
                                                    @endif
                                                    {{ $value->value }}
                                                </div>
                                            @endforeach
                                            @if($attribute->values->count() > 4)
                                                <span class="text-muted small align-self-center">+{{ $attribute->values->count() - 4 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" disabled {{ $attribute->is_required ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $attribute->status }}">
                                            {{ ucfirst($attribute->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('erp.variation-attributes.edit', $attribute->id) }}" 
                                               class="btn btn-action btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit small"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-action btn-outline-{{ $attribute->status === 'active' ? 'secondary' : 'success' }}"
                                                    onclick="toggleStatus({{ $attribute->id }})" 
                                                    title="{{ $attribute->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $attribute->status === 'active' ? 'pause' : 'play' }} small"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-action btn-outline-danger"
                                                    onclick="deleteAttribute({{ $attribute->id }})" 
                                                    title="Delete">
                                                <i class="fas fa-trash small"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <img src="/static/empty-state.svg" alt="Empty" style="width: 150px; opacity: 0.5; margin-bottom: 1rem;">
                                        <h5 class="text-muted">No attributes found</h5>
                                        <p class="text-muted small">Start by adding common attributes like "Size" or "Color"</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4 text-center">
                    <div class="bg-light-danger p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Delete Attribute?</h5>
                    <p class="text-muted">This action will remove the attribute and all its values. This might affect products that use these variations.</p>
                    <div class="d-flex gap-3 mt-4">
                        <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" method="POST" class="flex-grow-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">Confirm Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleStatus(id) {
            if (confirm('Toggle status for this attribute?')) {
                fetch(`/erp/variation-attributes/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                }).then(() => location.reload());
            }
        }

        function deleteAttribute(id) {
            const $m = $('#deleteModal');
            $('#deleteForm').attr('action', `/erp/variation-attributes/${id}`);
            new bootstrap.Modal($m[0]).show();
        }
    </script>
@endsection
