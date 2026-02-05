<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\BranchProductStock;
use App\Models\WarehouseProductStock;
use App\Models\EmployeeProductStock;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseItem;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        // Query PurchaseReturnItem instead of PurchaseReturn for detailed report
        $query = \App\Models\PurchaseReturnItem::with([
            'purchaseReturn.purchase.supplier',
            'purchaseReturn.purchase.bill', 
            'product.category', 
            'product.brand', 
            'product.season', 
            'product.gender',
            'purchaseItem.variation',
            'branch',
            'warehouse'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);

        // Calculate Totals before pagination
        // For items list, totalQty is sum of returned_qty of lines
        // TotalPrice is sum of line totals (returned_qty * unit_price)
        
        $totalQty = $query->sum('returned_qty');
        $totalPrice = $query->sum(\DB::raw('returned_qty * unit_price')); // Assuming simple calculation

        $items = $query->latest()->paginate(20)->appends($request->all());

        // Dropdown Data
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $seasons = \App\Models\Season::orderBy('name')->get();
        $genders = \App\Models\Gender::orderBy('name')->get();
        $products = \App\Models\Product::where('type', 'product')->orderBy('name')->get();
        $statuses = ['pending', 'approved', 'rejected', 'processed'];

        return view('erp.purchaseReturn.purchasereturnlist', compact(
            'items', 'statuses', 'suppliers', 'categories', 'brands', 'seasons', 'genders', 'products', 'reportType', 'startDate', 'endDate', 'totalQty', 'totalPrice'
        ));
    }

    public function exportExcel(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\PurchaseReturnItem::with([
            'purchaseReturn.purchase.supplier', 
            'purchaseReturn.purchase.bill', 
            'product.category', 
            'product.brand', 
            'product.season', 
            'product.gender',
            'purchaseItem.variation'
        ]);
        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'SL', 'Return Date', 'Return #', 'Original Inv #', 'Source', 'Supplier', 'Mobile', 
            'Category', 'Brand', 'Season', 'Gender', 'Product Name', 'Style #', 
            'Color', 'Size', 'Ret. Qty', 'Ret. Amount', 'Status'
        ];
        $exportData[] = $headers;

        foreach ($items as $index => $item) {
            $return = $item->purchaseReturn;
            if (!$return) continue;
            
            $purchase = $return->purchase;
            $product = $item->product;
            $variation = $item->purchaseItem ? $item->purchaseItem->variation : null;
            $supplier = $purchase ? $purchase->supplier : null;

            // Extract Color and Size
            $color = '-'; $size = '-';
            if ($variation && $variation->attributeValues) {
                foreach($variation->attributeValues as $val) {
                    $attrName = strtolower($val->attribute->name ?? '');
                    if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) {
                        $color = $val->value;
                    } elseif (str_contains($attrName, 'size')) {
                        $size = $val->value;
                    }
                }
            }

            // Source
            $source = 'N/A';
            if ($item->return_from_type == 'branch') {
                $branch = \App\Models\Branch::find($item->return_from_id);
                $source = 'Branch: ' . ($branch->name ?? $item->return_from_id);
            } elseif ($item->return_from_type == 'warehouse') {
                $warehouse = \App\Models\Warehouse::find($item->return_from_id);
                $source = 'Warehouse: ' . ($warehouse->name ?? $item->return_from_id);
            }

            $row = [
                $index + 1,
                $return->return_date,
                'RET-'.str_pad($return->id, 5, '0', STR_PAD_LEFT),
                $purchase ? ($purchase->bill->bill_number ?? 'P-'.$purchase->id) : 'N/A',
                $source,
                $supplier->name ?? 'N/A',
                $supplier->mobile ?? 'N/A',
                $product->category->name ?? 'N/A',
                $product->brand->name ?? 'N/A',
                $product->season->name ?? 'N/A',
                $product->gender->name ?? 'N/A',
                $product->name ?? 'N/A',
                $product->sku ?? $product->style_number ?? 'N/A',
                $color,
                $size,
                $item->returned_qty,
                number_format($item->total_price, 2),
                ucfirst($return->status)
            ];
            $exportData[] = $row;
        }

        $filename = 'purchase_return_audit_' . date('Y-m-d_His') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        foreach ($exportData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $value) {
                $sheet->setCellValue(chr(65 + $colIndex) . ($rowIndex + 1), $value);
            }
        }
        
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $filename);
        $writer->save($filePath);
        
        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    public function exportPdf(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\PurchaseReturnItem::with([
            'purchaseReturn.purchase.supplier', 
            'purchaseReturn.purchase.bill', 
            'product.category', 
            'product.brand', 
            'product.season', 
            'product.gender',
            'purchaseItem.variation'
        ]);
        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->orderBy('created_at', 'desc')->get();

        $filename = 'purchase_return_audit_' . date('Y-m-d_His') . '.pdf';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.purchaseReturn.report-pdf', [
            'items' => $items,
            'filters' => $request->all()
        ]);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->download($filename);
    }

    private function applyFilters($query, Request $request, $startDate = null, $endDate = null)
    {
        // Date Filtering on the related PurchaseReturn
        if ($startDate && $endDate) {
            $query->whereHas('purchaseReturn', function($q) use ($startDate, $endDate) {
                $q->whereBetween('return_date', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('purchaseReturn', function($q) use ($startDate) {
                $q->whereDate('return_date', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('purchaseReturn', function($q) use ($endDate) {
                $q->whereDate('return_date', '<=', $endDate);
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('purchaseReturn', function($q) use ($searchTerm) {
                $q->where('id', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('purchase', function($purchaseQuery) use ($searchTerm) {
                      $purchaseQuery->where('id', 'LIKE', "%{$searchTerm}%")
                           ->orWhereHas('bill', function($bq) use ($searchTerm) {
                               $bq->where('invoice_number', 'LIKE', "%{$searchTerm}%");
                           });
                  });
            });
        }

        // Filters from dropdowns
        if ($request->filled('supplier_id')) {
            $query->whereHas('purchaseReturn', function($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('purchaseReturn', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        if ($request->filled('purchase_id')) {
            $query->where('purchase_return_id', $request->purchase_id); // This might need adjustment if purchase_id refers to original purchase
            // If filtering by Original Purchase ID:
             $query->whereHas('purchaseReturn', function($q) use ($request) {
                $q->where('purchase_id', $request->purchase_id);
             });
        }

        // Filter by Product/Style/Category/Brand/Season/Gender
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);

        if ($request->filled('style_number') || $request->filled('category_id') || 
            $request->filled('brand_id') || $request->filled('season_id') || $request->filled('gender_id')) {
            
            $query->whereHas('product', function($q) use ($request) {
                if ($request->filled('style_number')) $q->where('style_number', 'like', '%' . $request->style_number . '%');
                if ($request->filled('category_id')) $q->where('category_id', $request->category_id);
                if ($request->filled('brand_id')) $q->where('brand_id', $request->brand_id);
                if ($request->filled('season_id')) $q->where('season_id', $request->season_id);
                if ($request->filled('gender_id')) $q->where('gender_id', $request->gender_id);
            });
        }

        return $query;
    }

    public function create()
    {
        $branches = Branch::all();
        $warehouses = Warehouse::all();

        return view('erp.purchaseReturn.create', compact('branches', 'warehouses'));
    }

    /**
     * Search purchases by invoice number for Ajax
     */
    public function searchInvoice(Request $request)
    {
        $invoiceNo = $request->invoice_no;
        if (!$invoiceNo) {
            return response()->json(['success' => false, 'message' => 'Invoice number is required.']);
        }

        // Strip '#' if user included it in search (e.g., #28 -> 28)
        $cleanInvoiceNo = ltrim($invoiceNo, '#');

        $purchase = Purchase::with(['supplier', 'bill', 'items.product', 'items.variation.attributeValues.attribute'])
            ->whereHas('bill', function($q) use ($invoiceNo, $cleanInvoiceNo) {
                $q->where('bill_number', $invoiceNo)
                  ->orWhere('bill_number', $cleanInvoiceNo);
            })
            ->orWhere('id', $cleanInvoiceNo)
            ->first();

        if (!$purchase) {
            return response()->json(['success' => false, 'message' => 'Purchase not found.']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $purchase->id,
                'invoice_no' => $purchase->bill->bill_number ?? 'PUR-' . $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'supplier_name' => $purchase->supplier->name ?? 'N/A',
                'purchase_date' => $purchase->purchase_date,
                'items' => $purchase->items->map(function($item) use ($purchase) {
                    $color = '-'; $size = '-';
                    if ($item->variation && $item->variation->attributeValues) {
                        foreach($item->variation->attributeValues as $val) {
                            $attrName = strtolower($val->attribute->name ?? '');
                            if (str_contains($attrName, 'color')) $color = $val->value;
                            elseif (str_contains($attrName, 'size')) $size = $val->value;
                        }
                    }

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'variation_id' => $item->variation_id,
                        'variation_name' => $item->variation->name ?? 'Standard',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'color' => $color,
                        'size' => $size,
                        'style_number' => $item->product->style_number ?? '-',
                        'location_type' => $purchase->ship_location_type ?? 'branch',
                        'location_id' => $purchase->location_id,
                    ];
                })
            ]
        ]);
    }

    /**
     * Search purchases by invoice number for Select2
     */
    public function searchPurchaseByInvoice(Request $request)
    {
        $query = $request->get('q', '');
        
        $purchases = Purchase::with(['supplier', 'bill'])
            ->where(function($q) use ($query) {
                $q->where('id', 'LIKE', "%{$query}%")
                  ->orWhereHas('bill', function($billQuery) use ($query) {
                      $billQuery->where('bill_number', 'LIKE', "%{$query}%");
                  });
            })
            ->limit(20)
            ->get();

        $results = $purchases->map(function($purchase) {
            $invoiceNo = $purchase->bill ? $purchase->bill->bill_number : $purchase->id;
            $supplierName = $purchase->supplier ? $purchase->supplier->name : 'N/A';
            
            return [
                'id' => $purchase->id,
                'text' => "#{$invoiceNo} - {$supplierName} - " . date('d M Y', strtotime($purchase->purchase_date))
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Get purchase items for a specific purchase
     */
    public function getPurchaseItems($purchaseId)
    {
        $purchase = Purchase::with(['items.product', 'items.variation', 'supplier'])->findOrFail($purchaseId);
        
        $items = $purchase->items->map(function($item) {
            $productName = $item->product ? $item->product->name : 'Unknown Product';
            if ($item->variation) {
                $productName .= ' - ' . $item->variation->name;
            }
            
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variation_id' => $item->variation_id,
                'product_name' => $productName,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ];
        });

        return response()->json([
            'success' => true,
            'purchase' => [
                'id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'supplier_name' => $purchase->supplier ? $purchase->supplier->name : 'N/A',
                'purchase_date' => $purchase->purchase_date,
            ],
            'items' => $items
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'supplier_id' => 'nullable|integer',
            'return_date' => 'required|date',
            'return_type' => 'required|in:refund,adjust_to_due,none',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            // Allow 0 here for validation, we'll skip them in the loop
            'items.*.returned_qty' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $request->purchase_id,
                'supplier_id' => $request->supplier_id ?? null,
                'return_date' => $request->return_date,
                'return_type' => $request->return_type,
                'status' => 'pending',
                'reason' => $request->reason,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $returnedQty = $item['returned_qty'] ?? 0;
                if ($returnedQty <= 0) continue;

                $returnItem = PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_item_id' => $item['purchase_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'returned_qty' => $returnedQty,
                    'unit_price' => $item['unit_price'],
                    'total_price' => $returnedQty * $item['unit_price'],
                    'reason' => $item['reason'] ?? null,
                    'return_from_type' => $item['return_from'] ?? null,
                    'return_from_id' => $item['from_id'] ?? null,
                ]);

                // If immediate processing (if we add a status field to the form later)
                if ($purchaseReturn->status === 'processed') {
                    $this->adjustStockForReturnItem($returnItem);
                }
            }

            DB::commit();
            return redirect()->route('purchaseReturn.list')->with('success', 'Purchase return created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $purchaseReturn = PurchaseReturn::with([
            'purchase', 
            'createdBy', 
            'approvedBy', 
            'items.product', 
            'items.purchaseItem',
            'items.branch',
            'items.warehouse',
            'items.employee'
        ])->findOrFail($id);

        return view('erp.purchaseReturn.show', compact('purchaseReturn'));
    }

    public function edit($id)
    {
        $purchaseReturn = PurchaseReturn::with([
            'purchase', 
            'supplier', 
            'items.product', 
            'items.purchaseItem',
            'items.branch',
            'items.warehouse',
            'items.employee'
        ])->findOrFail($id);

        // Check if return can be edited (only pending returns can be edited)
        if ($purchaseReturn->status !== 'pending') {
            return redirect()->route('purchaseReturn.show', $id)
                ->with('error', 'Only pending purchase returns can be edited.');
        }

        $branches = Branch::all();
        $warehouses = Warehouse::all();

        return view('erp.purchaseReturn.edit', compact('purchaseReturn', 'branches', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $purchaseReturn = PurchaseReturn::findOrFail($id);

        // Check if return can be updated (only pending returns can be updated)
        if ($purchaseReturn->status !== 'pending') {
            return redirect()->route('purchaseReturn.show', $id)
                ->with('error', 'Only pending purchase returns can be updated.');
        }

        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'supplier_id' => 'nullable|integer',
            'return_date' => 'required|date',
            'return_type' => 'required|in:refund,adjust_to_due,none',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.returned_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        // Sum returned quantities per product in this request
        $productReturnSums = [];
        foreach ($request->items as $item) {
            $productId = $item['product_id'];
            $productReturnSums[$productId] = ($productReturnSums[$productId] ?? 0) + $item['returned_qty'];
        }

        // For each product, check if total returned (including previous returns) exceeds purchased quantity
        foreach ($productReturnSums as $productId => $returnQty) {
            // Get purchased quantity for this product in this purchase
            $purchaseItem = PurchaseItem::where('purchase_id', $request->purchase_id)
                ->where('product_id', $productId)
                ->first();
            if (!$purchaseItem) {
                return back()->withErrors(["error" => "Product not found in purchase."])->withInput();
            }
            $purchasedQty = $purchaseItem->quantity;
            // Get previous returned quantity for this product in this purchase (excluding current return)
            $previousReturnedQty = PurchaseReturnItem::where('product_id', $productId)
                ->whereHas('purchaseReturn', function($q) use ($request, $id) {
                    $q->where('purchase_id', $request->purchase_id)
                      ->where('id', '!=', $id);
                })->sum('returned_qty');
            // Check
            if (($previousReturnedQty + $returnQty) > $purchasedQty) {
                return back()->withErrors(["error" => "Total returned quantity for product ID $productId exceeds purchased quantity."])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Update purchase return
            $purchaseReturn->update([
                'purchase_id' => $request->purchase_id,
                'supplier_id' => $request->supplier_id ?? null,
                'return_date' => $request->return_date,
                'return_type' => $request->return_type,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $purchaseReturn->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_item_id' => $item['purchase_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'returned_qty' => $item['returned_qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['returned_qty'] * $item['unit_price'],
                    'reason' => $item['reason'] ?? null,
                    'return_from_type' => $item['return_from'] ?? null,
                    'return_from_id' => $item['from_id'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('purchaseReturn.show', $id)->with('success', 'Purchase return updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function updateReturnStatus(Request $request, $returnId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,processed',
            'notes' => 'nullable|string|max:500'
        ]);

        $purchaseReturn = PurchaseReturn::with(['items'])->findOrFail($returnId);
        
        // Check if status can be updated
        if ($purchaseReturn->status === 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Purchase return is already processed and cannot be updated.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $purchaseReturn->status;
            $newStatus = $request->status;

            // Update the purchase return status
            $updateData = ['status' => $newStatus];

            // If status is being approved, set approved_by and approved_at
            if ($newStatus === 'approved') {
                $updateData['approved_by'] = Auth::id();
                $updateData['approved_at'] = now();
            }

            // Add notes if provided
            if ($request->filled('notes')) {
                $currentNotes = $purchaseReturn->notes ? $purchaseReturn->notes . "\n" : "";
                $updateData['notes'] = $currentNotes . "[" . now()->format('Y-m-d H:i:s') . "] Status changed to " . ucfirst($newStatus) . ": " . $request->notes;
            }

            $purchaseReturn->update($updateData);

            // If status is being processed, adjust stock
            if ($newStatus === 'processed') {
                foreach ($purchaseReturn->items as $item) {
                    $this->adjustStockForReturnItem($item);
                }
            }

            DB::commit();

            $statusMessage = match($newStatus) {
                'approved' => 'Purchase return has been approved successfully.',
                'rejected' => 'Purchase return has been rejected.',
                'processed' => 'Purchase return has been processed and stock has been adjusted.',
                default => 'Purchase return status has been updated.'
            };

            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'data' => [
                    'id' => $purchaseReturn->id,
                    'status' => $purchaseReturn->status,
                    'approved_by' => $purchaseReturn->approved_by,
                    'approved_at' => $purchaseReturn->approved_at
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase return status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust stock for a return item based on return_from_type and return_from_id
     */
    private function adjustStockForReturnItem($item)
    {
        $returnedQty = $item->returned_qty;
        $productId = $item->product_id;
        $variationId = $item->variation_id ?? null;
        $returnFromType = $item->return_from_type;
        $returnFromId = $item->return_from_id;

        switch ($returnFromType) {
            case 'branch':
                if ($variationId) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('branch_id', $returnFromId)
                        ->whereNull('warehouse_id')
                        ->first();
                    if ($stock) {
                        $stock->decrement('quantity', $returnedQty);
                    } else {
                        \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'branch_id' => $returnFromId,
                            'quantity' => -$returnedQty
                        ]);
                    }
                } else {
                    $stock = BranchProductStock::where('branch_id', $returnFromId)
                        ->where('product_id', $productId)
                        ->first();
                    
                    if ($stock) {
                        $stock->decrement('quantity', $returnedQty);
                    } else {
                        // Create negative stock record if doesn't exist
                        BranchProductStock::create([
                            'branch_id' => $returnFromId,
                            'product_id' => $productId,
                            'quantity' => -$returnedQty
                        ]);
                    }
                }
                break;

            case 'warehouse':
                if ($variationId) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('warehouse_id', $returnFromId)
                        ->whereNull('branch_id')
                        ->first();
                    if ($stock) {
                        $stock->decrement('quantity', $returnedQty);
                    } else {
                        \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'warehouse_id' => $returnFromId,
                            'quantity' => -$returnedQty
                        ]);
                    }
                } else {
                    $stock = WarehouseProductStock::where('warehouse_id', $returnFromId)
                        ->where('product_id', $productId)
                        ->first();
                    
                    if ($stock) {
                        $stock->decrement('quantity', $returnedQty);
                    } else {
                        // Create negative stock record if doesn't exist
                        WarehouseProductStock::create([
                            'warehouse_id' => $returnFromId,
                            'product_id' => $productId,
                            'quantity' => -$returnedQty
                        ]);
                    }
                }
                break;

            case 'employee':
                $stock = EmployeeProductStock::where('employee_id', $returnFromId)
                    ->where('product_id', $productId)
                    ->first();
                
                if ($stock) {
                    $stock->decrement('quantity', $returnedQty);
                } else {
                    // Create negative stock record if doesn't exist
                    EmployeeProductStock::create([
                        'employee_id' => $returnFromId,
                        'product_id' => $productId,
                        'quantity' => -$returnedQty
                    ]);
                }
                break;

            default:
                throw new \Exception("Invalid return_from_type: {$returnFromType}");
        }
    }

    public function getStockByType(Request $request, $productId, $fromId)
    {
        $stock = [];
        if($request->return_from == 'branch')
        {
            $stock = BranchProductStock::where('branch_id', $fromId)->where('product_id', $productId)->first();
        } else if($request->return_from == 'warehouse')
        {
            $stock = WarehouseProductStock::where('warehouse_id', $fromId)->where('product_id', $productId)->first();
        }

        return response()->json($stock);
    }
}
