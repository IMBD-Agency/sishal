@extends('erp.master')

@section('title', 'Create Variation Attribute')

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
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                overflow: hidden;
                margin-bottom: 2rem;
            }

            .premium-card-header {
                background: #fff;
                border-bottom: 1px solid var(--gray-100);
                padding: 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .form-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--gray-700);
                margin-bottom: 0.5rem;
            }

            .value-row {
                background: var(--gray-50);
                border: 1px solid var(--gray-200);
                border-radius: 12px;
                padding: 1.25rem;
                margin-bottom: 1rem;
                transition: all 0.2s;
            }

            .value-row:hover {
                border-color: var(--primary-indigo);
                background: #fff;
            }

            .quick-add-box {
                background: #f8fafc;
                border: 2px dashed #cbd5e1;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
        </style>

        <div class="container-fluid px-4 py-4">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('erp.variation-attributes.index') }}" class="text-decoration-none text-muted">Attributes</a></li>
                    <li class="breadcrumb-item active fw-bold text-dark">Create Attribute</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">New Attribute</h2>
                    <p class="text-muted mb-0">Define a new property and its possible values.</p>
                </div>
                <a href="{{ route('erp.variation-attributes.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <form action="{{ route('erp.variation-attributes.store') }}" method="POST" enctype="multipart/form-data" id="attributeForm">
                @csrf
                
                <div class="row g-4">
                    <!-- Left Pane: Info -->
                    <div class="col-lg-4">
                        <div class="premium-card">
                            <div class="premium-card-header">
                                <h5 class="mb-0 fw-bold">1. General Info</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label">Attribute Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="e.g. Size or Color" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="slug" id="slug" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Optional notes..."></textarea>
                                </div>
                                <hr class="my-4">
                                <div class="mb-3">
                                    <label class="form-label">Configuration</label>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1" checked>
                                        <label class="form-check-label" for="is_required">Required in Products</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_color" id="is_color" value="1">
                                        <label class="form-check-label" for="is_color">Color-based Attribute</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Pane: Values -->
                    <div class="col-lg-8">
                        <div class="premium-card">
                            <div class="premium-card-header">
                                <h5 class="mb-0 fw-bold">2. Define Values</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleQuickAdd()">
                                    <i class="fas fa-bolt me-1"></i> Quick Add
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <!-- Quick Add Box -->
                                <div id="quickAddBox" class="quick-add-box" style="display: none;">
                                    <label class="form-label fw-bold">Bulk Paste Values</label>
                                    <p class="text-muted small">Enter one value per line (e.g. Red, Blue, Green)</p>
                                    <textarea id="bulkValues" class="form-control mb-3" rows="5" placeholder="S&#10;M&#10;L&#10;XL"></textarea>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="processQuickAdd()">
                                            Add These Values
                                        </button>
                                    </div>
                                </div>

                                <div id="valuesContainer">
                                    <!-- Rows added here -->
                                </div>

                                <button type="button" class="btn btn-light w-100 py-3 border-dashed mt-2" onclick="addValueRow()">
                                    <i class="fas fa-plus-circle me-2 text-primary"></i>Click to add a value
                                </button>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i>SAVE ATTRIBUTE
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let valueIndex = 0;

        $(document).ready(function() {
            // Auto-slug
            $('#name').on('input', function() {
                $('#slug').val($(this).val().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, ''));
            });

            // Color toggle
            $('#is_color').on('change', function() {
                const checked = $(this).is(':checked');
                $('.color-input-col').toggle(checked);
            });

            // Add first row
            addValueRow();
        });

        function addValueRow(initialValue = '') {
            const isColor = $('#is_color').is(':checked');
            const rowHtml = `
                <div class="value-row" id="vrow_${valueIndex}">
                    <div class="row align-items-end g-3">
                        <div class="col">
                            <label class="form-label small">Display Value</label>
                            <input type="text" name="values[${valueIndex}][value]" class="form-control" value="${initialValue}" placeholder="e.g. XL or Crimson" required>
                        </div>
                        <div class="col-md-3 color-input-col" style="display: ${isColor ? 'block' : 'none'}">
                            <label class="form-label small">Color Picker</label>
                            <input type="color" name="values[${valueIndex}][color_code]" class="form-control form-control-color w-100" value="#4f46e5">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Sort</label>
                            <input type="number" name="values[${valueIndex}][sort_order]" class="form-control" value="${valueIndex}">
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-danger btn-sm border-0 mt-3" onclick="$('#vrow_${valueIndex}').remove()">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#valuesContainer').append(rowHtml);
            valueIndex++;
        }

        function toggleQuickAdd() {
            $('#quickAddBox').slideToggle();
        }

        function processQuickAdd() {
            const vals = $('#bulkValues').val().split('\n').filter(v => v.trim() !== '');
            if(vals.length > 0) {
                // Remove the empty first row if it exists and hasn't been edited
                const firstRow = $('#valuesContainer .value-row').first();
                if(firstRow.length && firstRow.find('input[type="text"]').val() === '') {
                    firstRow.remove();
                }
                vals.forEach(v => addValueRow(v.trim()));
                $('#bulkValues').val('');
                $('#quickAddBox').slideUp();
            }
        }
    </script>
@endsection
