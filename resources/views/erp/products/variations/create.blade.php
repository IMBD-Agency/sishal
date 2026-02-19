@extends('erp.master')

@section('title', 'Create Variations - ' . $product->name)

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            :root {
                --primary-teal: #17a2b8;
                --primary-hover: #138496;
                --gray-50: #f9fafb;
                --gray-100: #f3f4f6;
                --gray-200: #e5e7eb;
                --gray-700: #374151;
            }

            .premium-card {
                background: #fff;
                border: 1px solid var(--gray-200);
                border-radius: 12px;
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

            .attribute-value-tag {
                cursor: pointer;
                padding: 6px 12px;
                border-radius: 6px;
                border: 1px solid var(--gray-200);
                background: #fff;
                font-size: 0.875rem;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s;
                user-select: none;
            }

            .attribute-value-tag:hover {
                border-color: var(--primary-teal);
                background: var(--gray-50);
            }

            .attribute-value-tag.selected {
                background: #eff6ff;
                border-color: #3b82f6;
                color: #1e40af;
                box-shadow: 0 0 0 1px #3b82f6;
            }

            .color-preview {
                width: 14px;
                height: 14px;
                border-radius: 50%;
                border: 1px solid rgba(0,0,0,0.1);
            }

            .variant-grid-table th {
                background: var(--gray-50);
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--gray-700);
                padding: 1rem;
            }

            .variant-grid-table td {
                padding: 0.75rem 1rem;
                vertical-align: middle;
            }

            .form-control-minimal {
                border: 1px solid transparent;
                background: transparent;
                padding: 4px 8px;
                border-radius: 4px;
                transition: all 0.2s;
            }

            .form-control-minimal:focus, .form-control-minimal:hover {
                background: #fff;
                border-color: var(--gray-200);
            }

            .bulk-apply-bar {
                background: #f8fafc;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1.5rem;
                display: flex;
                gap: 1rem;
                align-items: flex-end;
            }
        </style>

        <div class="container-fluid px-4 py-4">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('product.list') }}" class="text-decoration-none">Products</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('erp.products.variations.index', $product->id) }}" class="text-decoration-none">{{ $product->name }}</a></li>
                    <li class="breadcrumb-item active">Add Variations</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Create Variations</h2>
                    <p class="text-muted mb-0">Select attributes to generate product combinations.</p>
                </div>
                <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <form action="{{ route('erp.products.variations.store', $product->id) }}" method="POST" enctype="multipart/form-data" id="variationForm">
                @csrf
                
                <!-- Attribute Selection Area -->
                <div class="premium-card">
                    <div class="premium-card-header">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i>1. Select Attributes & Values</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            @foreach($attributes as $attribute)
                                <div class="col-12 attribute-section" data-attribute-id="{{ $attribute->id }}" data-attribute-name="{{ $attribute->name }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0">
                                            {{ $attribute->name }}
                                            @if($attribute->is_required) <span class="text-danger ms-1">*</span> @endif
                                        </h6>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary py-0" onclick="selectAllAttr({{ $attribute->id }})">All</button>
                                            <button type="button" class="btn btn-outline-secondary py-0" onclick="deselectAllAttr({{ $attribute->id }})">None</button>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($attribute->activeValues as $val)
                                            <div class="attribute-value-tag" 
                                                 data-value-id="{{ $val->id }}" 
                                                 data-value-text="{{ $val->value }}"
                                                 onclick="toggleValue(this)">
                                                @if($attribute->is_color && $val->color_code)
                                                    <span class="color-preview" style="background-color: {{ $val->color_code }}"></span>
                                                @endif
                                                {{ $val->value }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Variations List Area -->
                <div class="premium-card" id="variationsArea" style="display: none;">
                    <div class="premium-card-header d-flex justify-content-between">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>2. Configure Combinations</h5>
                        <span class="badge bg-primary rounded-pill px-3 py-2" id="totalCombinations">0 Variations</span>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Bulk Apply Bar -->
                        <div class="bulk-apply-bar">

                            <div class="flex-grow-1">
                                <label class="form-label small fw-bold text-muted">Bulk Apply Cost</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" id="bulkCost" class="form-control" placeholder="0.00" step="0.01">
                                    <button class="btn btn-outline-primary" type="button" onclick="applyBulk('cost')">Apply</button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label small fw-bold text-muted">Sync All Items</label>
                                <button class="btn btn-sm btn-outline-secondary d-block w-100" type="button" onclick="syncAllSkus()">
                                    <i class="fas fa-sync-alt me-1"></i>Regen Style Nos
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table variant-grid-table">
                                <thead>
                                    <tr>
                                        <th width="80">Active</th>
                                        <th width="80">Default</th>
                                        <th>Combination</th>
                                        <th>Style No</th>
                                        <th>Cost</th>
                                        <th>Image</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="variationTableBody">
                                    <!-- Rows added dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Button Area -->
                <div class="text-end mb-5">
                    <button type="submit" class="btn btn-primary px-5 py-3 fw-bold shadow-sm" id="saveButton" style="display: none;">
                        <i class="fas fa-save me-2"></i>SAVE ALL VARIATIONS
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Template Row for Cloning (optional but we'll use JS template strings) -->

    <script>
        const productSku = "{{ $product->sku }}";
        const defaultPrice = "{{ $product->price }}";
        const defaultCost = "{{ $product->cost }}";
        const selections = {};
        
        function selectAllAttr(attrId) {
            $(`.attribute-section[data-attribute-id="${attrId}"] .attribute-value-tag:not(.selected)`).click();
        }

        function deselectAllAttr(attrId) {
            $(`.attribute-section[data-attribute-id="${attrId}"] .attribute-value-tag.selected`).click();
        }

        function toggleValue(el) {
            const $tag = $(el);
            const $section = $tag.closest('.attribute-section');
            const attrId = $section.data('attribute-id');
            const valId = $tag.data('value-id');
            const valText = $tag.data('value-text');

            $tag.toggleClass('selected');

            if (!selections[attrId]) selections[attrId] = [];

            if ($tag.hasClass('selected')) {
                selections[attrId].push({ id: valId, text: valText });
            } else {
                selections[attrId] = selections[attrId].filter(v => v.id != valId);
            }

            regenerateVariations();
        }

        function regenerateVariations() {
            const activeAttributes = Object.keys(selections).filter(id => selections[id].length > 0);
            
            if (activeAttributes.length === 0) {
                $('#variationsArea').fadeOut();
                $('#saveButton').fadeOut();
                return;
            }

            // Cartesian Product
            let combinations = [[]];
            activeAttributes.forEach(attrId => {
                let newCombos = [];
                combinations.forEach(combo => {
                    selections[attrId].forEach(val => {
                        let newCombo = [...combo, { attrId, valId: val.id, text: val.text }];
                        newCombos.push(newCombo);
                    });
                });
                combinations = newCombos;
            });

            const $tbody = $('#variationTableBody');
            $tbody.empty();

            combinations.forEach((combo, index) => {
                const name = combo.map(c => c.text).join(' - ');
                const slugSuffix = combo.map(c => c.text.toUpperCase().substring(0, 3)).join('');
                const sku = `${productSku}-${slugSuffix}`;
                
                const row = `
                    <tr class="variant-row" id="row_${index}">
                        <td>
                            <div class="form-check form-switch ps-5">
                                <input class="form-check-input" type="checkbox" name="vars[${index}][active]" checked value="1">
                            </div>
                        </td>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_default_index" value="${index}" ${index === 0 ? 'checked' : ''}>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold">${name}</span>
                            <input type="hidden" name="vars[${index}][name]" value="${name}">
                            ${combo.map(c => `<input type="hidden" name="vars[${index}][attributes][${c.attrId}]" value="${c.valId}">`).join('')}
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm form-control-minimal" name="vars[${index}][sku]" value="${sku}">
                        </td>

                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-transparent border-0 text-muted">৳</span>
                                <input type="number" class="form-control form-control-minimal cost-input" name="vars[${index}][cost]" value="${defaultCost}" step="0.01">
                            </div>
                        </td>
                        <td>
                            <input type="file" class="form-control form-control-sm" name="vars[${index}][image]" accept="image/*">
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm text-danger" onclick="$(this).closest('tr').remove(); updateCount();">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(row);
            });

            $('#totalCombinations').text(combinations.length + ' Variations');
            $('#variationsArea').fadeIn();
            $('#saveButton').fadeIn();
        }

        function applyBulk(type) {
            const val = $(`#bulk${type.charAt(0).toUpperCase() + type.slice(1)}`).val();
            if (val === '') return;
            $(`.${type}-input`).val(val);
        }

        function syncAllSkus() {
            // Regeneration logic if needed, but they are already generated on selection
            regenerateVariations();
        }

        function updateCount() {
            const count = $('#variationTableBody tr').length;
            $('#totalCombinations').text(count + ' Variations');
            if (count === 0) {
                $('#variationsArea').fadeOut();
                $('#saveButton').fadeOut();
            }
        }

        $('#variationForm').on('submit', function() {
            $(this).find('button[type="submit"]')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>PROCESSSING...');
        });
    </script>
@endsection
