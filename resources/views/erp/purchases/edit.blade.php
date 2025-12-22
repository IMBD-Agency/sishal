@extends('erp.master')

@section('title', 'Edit Assign')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <h2 class="mb-4">Edit Assign</h2>
            <!-- Select2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <form id="purchaseForm" action="{{ route('purchase.update', $purchase->id) }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <input type="hidden" name="supplier_id" value="{{ $purchase->supplier_id ?? '' }}">
                    <div class="col-md-6">
                        <label class="form-label">Ship Location Type</label>
                        <select name="ship_location_type" id="ship_location_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="branch" {{ $purchase->ship_location_type == 'branch' ? 'selected' : '' }}>Branch</option>
                            <option value="warehouse" {{ $purchase->ship_location_type == 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="location_id" class="form-label">Location</label>
                        <select name="location_id" id="location_id" class="form-select" required>
                            <option value="">Select Location</option>
                            @php
                                $locations = $purchase->ship_location_type == 'branch' ? $branches : $warehouses;
                            @endphp
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $purchase->location_id == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="purchase_date" class="form-label">Assign Date</label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ $purchase->purchase_date }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="pending" @if($purchase->status == 'pending') selected @endif>Pending</option>
                            <option value="received" @if($purchase->status == 'received') selected @endif>Received</option>
                            <option value="cancelled" @if($purchase->status == 'cancelled') selected @endif>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ $purchase->notes }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Items</label>
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $i => $item)
                                <tr>
                                    <td>
                                        <select
                                            name="items[{{ $i }}][product_id]"
                                            class="form-select product-select"
                                            required
                                            data-selected-id="{{ $item->product_id }}"
                                            data-selected-text="{{ $item->product->name ?? '' }}"
                                        ></select>
                                        <select
                                            name="items[{{ $i }}][variation_id]"
                                            class="form-select mt-1 variation-select {{ $item->variation_id ? '' : 'd-none' }}"
                                            data-initial-variation-id="{{ $item->variation_id ?? '' }}"
                                        ></select>
                                        <div class="small text-muted mt-1 stock-indicator"></div>
                                    </td>
                                    <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control quantity" min="0.01" step="0.01" value="{{ $item->quantity }}" required></td>
                                    <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control unit_price" min="0" step="0.01" value="{{ $item->unit_price }}" required></td>
                                    <td class="item-total">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row" {{ $i == 0 ? 'disabled' : '' }}>&times;</button></td>
                                </tr>
                                <tr>
                                    <td colspan="6">
                                        <textarea class="form-control description w-80" name="items[{{ $i }}][description]" placeholder="Description">{{ $item->description }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary btn-sm" id="addItemRow">Add Item</button>

                    <!-- Summary Section -->
                    <div class="row justify-content-end mt-3">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Subtotal</th>
                                    <td id="subtotalCell">0.00</td>
                                </tr>
                                <tr>
                                    <th>Total Discount</th>
                                    <td id="totalDiscountCell">0.00</td>
                                </tr>
                                <tr>
                                    <th>Tax (5%)</th>
                                    <td id="taxCell">0.00</td>
                                </tr>
                                <tr>
                                    <th>Grand Total</th>
                                    <td id="grandTotalCell">0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mb-3 text-end">
                    <button type="submit" class="btn btn-primary">Update Assign</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Initialize Select2 with AJAX product search (name + SKU)
        function initProductSelect2(selector) {
            $(selector).select2({
                placeholder: 'Search product by name or SKU',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('products.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        const results = data.map(function (item) {
                            let label = item.name;
                            if (item.sku) {
                                label += ' (' + item.sku + ')';
                            }
                            return {
                                id: item.id,
                                text: label
                            };
                        });
                        return { results: results };
                    },
                    cache: true
                }
            });
        }

        // Helper to set initial value for AJAX Select2
        function setSelect2Value($select, id, text) {
            if (!id) return;
            if ($select.find("option[value='" + id + "']").length === 0) {
                var newOption = new Option(text || ('Product #' + id), id, true, true);
                $select.append(newOption).trigger('change');
            } else {
                $select.val(id).trigger('change');
            }
        }

        $(document).ready(function() {
            // Initialize existing product selects with current values
            $('.product-select').each(function() {
                const $select = $(this);
                const id = $select.data('selected-id');
                const text = $select.data('selected-text');
                initProductSelect2($select);
                setSelect2Value($select, id, text);
            });
        });

        // Re-initialize Select2 for new product selects after adding a row
        function reinitProductSelect2() {
            initProductSelect2('.product-select');
        }

        // Fetch product price and current stock for a row
        function handleProductChange(selectEl) {
            const productId = selectEl.value;
            const row = selectEl.closest('tr');
            const unitPriceInput = row ? row.querySelector('.unit_price') : null;
            const stockIndicator = selectEl.closest('td').querySelector('.stock-indicator');
            const locationType = document.getElementById('ship_location_type').value;
            const locationId = document.getElementById('location_id').value;

            if (!productId) {
                if (stockIndicator) stockIndicator.textContent = '';
                return;
            }

            // Auto-fill unit price (only if empty)
            $.get('{{ url('/erp/products') }}/' + productId + '/price', function (resp) {
                if (unitPriceInput && !unitPriceInput.value && resp && typeof resp.price !== 'undefined') {
                    unitPriceInput.value = resp.price;
                    updateTotals();
                }
            });

            // Show current stock at selected location
            if (!locationType || !locationId) {
                if (stockIndicator) {
                    stockIndicator.textContent = 'Select location to see current stock.';
                }
                return;
            }

            $.get('{{ url('/erp/order/product-stocks') }}/' + productId, function (resp) {
                if (!resp || !resp.success || !Array.isArray(resp.stocks)) {
                    if (stockIndicator) stockIndicator.textContent = '';
                    return;
                }
                const stocks = resp.stocks;
                let match = null;
                if (locationType === 'branch') {
                    match = stocks.find(s => s.type === 'branch' && String(s.branch_id) === String(locationId));
                } else if (locationType === 'warehouse') {
                    match = stocks.find(s => s.type === 'warehouse' && String(s.warehouse_id) === String(locationId));
                }
                if (stockIndicator) {
                    const qty = match ? match.quantity : 0;
                    stockIndicator.textContent = 'Current stock here: ' + qty;
                }
            });
        }
        // Data for locations
        const branches = @json($branches);
        const warehouses = @json($warehouses);

        function populateLocations(type) {
            const select = document.getElementById('location_id');
            select.innerHTML = '<option value="">Select Location</option>';
            let data = [];
            if (type === 'branch') data = branches;
            else if (type === 'warehouse') data = warehouses;
            data.forEach(loc => {
                select.innerHTML += `<option value="${loc.id}" ${'{{ $purchase->location_id }}' == loc.id ? 'selected' : ''}>${loc.name}</option>`;
            });
        }
        document.getElementById('ship_location_type').addEventListener('change', function() {
            populateLocations(this.value);
        });

        // Dynamic items
        let itemIndex = {{ count($purchase->items) }};
        document.getElementById('addItemRow').addEventListener('click', function() {
            const tbody = document.querySelector('#itemsTable tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-select product-select" required></select>
                    <select name="items[${itemIndex}][variation_id]" class="form-select mt-1 variation-select d-none"></select>
                    <div class="small text-muted mt-1 stock-indicator"></div>
                </td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" min="0.01" step="0.01" required></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit_price" min="0" step="0.01" required></td>
                <td class="item-total">0.00</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
            `;
            tbody.appendChild(row);
            // Add description row
            const descRow = document.createElement('tr');
            descRow.innerHTML = `
                <td colspan="6">
                    <textarea class="form-control description" name="items[${itemIndex}][description]" placeholder="Description"></textarea>
                </td>
            `;
            tbody.appendChild(descRow);
            itemIndex++;
            reinitProductSelect2();
        });
        document.querySelector('#itemsTable').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                const itemRow = e.target.closest('tr');
                const descRow = itemRow.nextElementSibling;
                itemRow.remove();
                if (descRow && descRow.querySelector('textarea.description')) {
                    descRow.remove();
                }
            }
        });
        // Calculate item total and update summary
        function updateTotals() {
            let subtotal = 0;
            let totalDiscount = 0;
            document.querySelectorAll('#itemsTable tbody tr').forEach((row, idx, rows) => {
                // Only process item rows (not description rows)
                if (!row.querySelector('.quantity')) return;
                const qty = parseFloat(row.querySelector('.quantity')?.value) || 0;
                const price = parseFloat(row.querySelector('.unit_price')?.value) || 0;
                let total = (qty * price) ;
                if (total < 0) total = 0;
                row.querySelector('.item-total').textContent = total.toFixed(2);
                subtotal += qty * price;
            });
            const tax = ((subtotal) * 0.05 > 0) ? (subtotal) * 0.05 : 0;
            const grandTotal = (subtotal) + tax;
            document.getElementById('subtotalCell').textContent = subtotal.toFixed(2);
            document.getElementById('taxCell').textContent = tax.toFixed(2);
            document.getElementById('grandTotalCell').textContent = grandTotal.toFixed(2);
        }
        document.querySelector('#itemsTable').addEventListener('input', function(e) {
            if (
                e.target.classList.contains('quantity') ||
                e.target.classList.contains('unit_price')
            ) {
                updateTotals();
            }
        });

        // When product / variation changes, fetch price + stock info
        document.querySelector('#itemsTable').addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select')) {
                handleProductChange(e.target);
            }
            if (e.target.classList.contains('variation-select')) {
                const selectEl = e.target;
                const row = selectEl.closest('tr');
                const unitPriceInput = row ? row.querySelector('.unit_price') : null;
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                if (unitPriceInput && selectedOption) {
                    const price = selectedOption.getAttribute('data-price');
                    if (price) {
                        unitPriceInput.value = price;
                        updateTotals();
                    }
                }
            }
        });
    </script>
@endsection 