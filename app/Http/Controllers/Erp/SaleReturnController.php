<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaleReturn;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Pos;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleReturnController extends Controller
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

        $query = \App\Models\SaleReturnItem::whereHas('saleReturn')->with([
            'saleReturn.customer',
            'saleReturn.posSale',
            'saleReturn.branch',
            'product.category',
            'product.brand',
            'product.season',
            'product.gender',
            'variation.attributeValues.attribute',
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);

        $totalQty = $query->sum('returned_qty');
        $totalAmount = $query->sum('total_price');

        $items = $query->latest()->paginate(20)->appends($request->all());
        
        $branches = Branch::all();
        $customers = Customer::orderBy('name')->get();
        $products = \App\Models\Product::orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $seasons = \App\Models\Season::orderBy('name')->get();
        $genders = \App\Models\Gender::orderBy('name')->get();

        return view('erp.saleReturn.salereturnlist', compact(
            'items', 'branches', 'customers', 'products', 'categories', 'brands', 'seasons', 'genders',
            'reportType', 'startDate', 'endDate', 'totalQty', 'totalAmount'
        ));
    }

    public function exportExcel(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), $request->get('month', date('m')), 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\SaleReturnItem::with([
            'saleReturn.customer', 'saleReturn.posSale', 'saleReturn.branch',
            'product.category', 'product.brand', 'product.season', 'product.gender',
            'variation.attributeValues.attribute'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Serial No', 'Date', 'R-Inv. No.', 'S-Inv. No.', 'Customer', 'Mobile', 'Outlet', 
            'Category', 'Brand', 'Season', 'Gender', 'Product Name', 'Style Number', 'Color', 'Size', 
            'Qty', 'Total Amount'
        ];
        
        $sheet->fromArray([$headers], NULL, 'A1');
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);

        $rowNum = 2;
        foreach ($items as $index => $item) {
            $return = $item->saleReturn;
            $product = $item->product;
            $variation = $item->variation;
            
            $color = '-'; $size = '-';
            if ($variation && $variation->attributeValues) {
                foreach($variation->attributeValues as $val) {
                    $attrName = strtolower($val->attribute->name ?? '');
                    if (str_contains($attrName, 'color')) $color = $val->value;
                    elseif (str_contains($attrName, 'size')) $size = $val->value;
                }
            }

            $data = [
                $index + 1,
                $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') : '-',
                '#SR-' . str_pad($return->id, 5, '0', STR_PAD_LEFT),
                $return->posSale->sale_number ?? '-',
                $return->customer->name ?? 'Walk-in',
                $return->customer->phone ?? '-',
                $return->branch->name ?? '-',
                $product->category->name ?? '-',
                $product->brand->name ?? '-',
                $product->season->name ?? '-',
                $product->gender->name ?? '-',
                $product->name ?? '-',
                $product->style_number ?? '-',
                $color,
                $size,
                $item->returned_qty,
                $item->total_price
            ];
            $sheet->fromArray([$data], NULL, 'A' . $rowNum);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'sale_return_report_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), $request->get('month', date('m')), 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\SaleReturnItem::with([
            'saleReturn.customer', 'saleReturn.posSale', 'saleReturn.branch',
            'product.category', 'product.brand', 'product.season', 'product.gender',
            'variation.attributeValues.attribute'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->latest()->get();

        $pdf = Pdf::loadView('erp.saleReturn.export-pdf', compact('items', 'reportType', 'startDate', 'endDate'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'sale_return_report_' . date('Ymd_His') . '.pdf';
        if ($request->input('action') === 'print') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    private function applyFilters($query, Request $request, $startDate = null, $endDate = null)
    {
        // Date Filtering
        if ($startDate && $endDate) {
            $query->whereHas('saleReturn', function($q) use ($startDate, $endDate) {
                $q->whereBetween('return_date', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('saleReturn', function($q) use ($startDate) {
                $q->whereDate('return_date', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('saleReturn', function($q) use ($endDate) {
                $q->whereDate('return_date', '<=', $endDate);
            });
        }

        // Search by sale number / invoice
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('saleReturn.posSale', function($sq) use ($search) {
                    $sq->where('sale_number', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('saleReturn.customer', function($cq) use ($search) {
                    $cq->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            });
        }

        // Filters from dropdowns
        if ($request->filled('branch_id')) {
            $query->whereHas('saleReturn', function($q) use ($request) {
                $q->where('return_to_id', $request->branch_id)->where('return_to_type', 'branch');
            });
        }
        if ($request->filled('customer_id')) {
            $query->whereHas('saleReturn', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('saleReturn', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        
        // Product Filters
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

    public function create(Request $request)
    {
        $customers = Customer::all();
        $posSales = Pos::all();
        $invoices = Invoice::all();
        $products = \App\Models\Product::all();
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        
        // Handle pre-selected POS sale from query parameter
        $selectedPosSale = null;
        if ($request->has('pos_sale_id')) {
            $selectedPosSale = Pos::with(['customer', 'items.product', 'items.variation', 'branch', 'invoice'])
                ->find($request->pos_sale_id);
        }
        
        return view('erp.saleReturn.create', compact('customers', 'posSales', 'invoices', 'products', 'branches', 'warehouses', 'selectedPosSale'));
    }

    public function searchInvoice(Request $request)
    {
        $invoiceNo = $request->invoice_no;
        if (!$invoiceNo) {
            return response()->json(['success' => false, 'message' => 'Invoice number is required.']);
        }

        $sale = Pos::with(['customer', 'items.product', 'items.variation.attributeValues.attribute', 'branch', 'invoice'])
            ->where('sale_number', $invoiceNo)
            ->first();

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer_id' => $sale->customer_id,
                'customer_name' => $sale->customer->name ?? 'Walk-in',
                'customer_phone' => $sale->customer->phone ?? '-',
                'branch_id' => $sale->branch_id,
                'items' => $sale->items->map(function($item) {
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
                    ];
                })
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'pos_sale_id' => 'nullable|exists:pos,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'return_date' => 'required|date',
            'refund_type' => 'required|in:none,cash,bank,credit',
            'return_to_type' => 'required|in:branch,warehouse,employee',
            'return_to_id' => 'required|integer',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.returned_qty' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data = $request->except(['items', 'status']);
        $data['status'] = 'pending';
        $saleReturn = SaleReturn::create($data);

        foreach ($request->items as $item) {
            $returnedQty = $item['returned_qty'] ?? 0;
            if ($returnedQty <= 0) continue;

            $returnItem = \App\Models\SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'sale_item_id' => $item['sale_item_id'] ?? null,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'returned_qty' => $returnedQty,
                'unit_price' => $item['unit_price'],
                'total_price' => $returnedQty * $item['unit_price'],
                'reason' => $item['reason'] ?? null,
            ]);

            if ($saleReturn->status === 'processed') {
                $this->addStockForReturnItem($saleReturn, $returnItem);
            }
        }

        return redirect()->route('saleReturn.list')->with('success', 'Sale return created successfully.');
    }

    public function show($id)
    {
        $saleReturn = SaleReturn::with([
            'customer',
            'posSale',
            'items.product',
            'items.variation',
            'employee.user',
            'branch',
            'warehouse'
        ])->findOrFail($id);
        return view('erp.saleReturn.show', compact('saleReturn'));
    }

    public function edit($id)
    {
        $saleReturn = SaleReturn::with(['items', 'employee.user'])->findOrFail($id);
        $customers = Customer::all();
        $posSales = Pos::all();
        $invoices = Invoice::all();
        $products = \App\Models\Product::all();
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        return view('erp.saleReturn.edit', compact('saleReturn', 'customers', 'posSales', 'invoices', 'products', 'branches', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'pos_sale_id' => 'nullable|exists:pos,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'return_date' => 'required|date',
            'refund_type' => 'required|in:none,cash,bank,credit',
            'return_to_type' => 'required|in:branch,warehouse,employee',
            'return_to_id' => 'required|integer',
            'reason' => 'nullable|string',
            'processed_by' => 'nullable|exists:users,id',
            'processed_at' => 'nullable|date',
            'account_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.returned_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);
        $saleReturn = SaleReturn::findOrFail($id);
        $saleReturn->update($request->except(['items', 'status']));
        // Remove old items
        $saleReturn->items()->delete();
        // Add new items
        foreach ($request->items as $item) {
            \App\Models\SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'sale_item_id' => $item['sale_item_id'] ?? null,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'returned_qty' => $item['returned_qty'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['returned_qty'] * $item['unit_price'],
                'reason' => $item['reason'] ?? null,
            ]);
        }
        return redirect()->route('saleReturn.list')->with('success', 'Sale return updated successfully.');
    }

    public function destroy($id)
    {
        $saleReturn = SaleReturn::findOrFail($id);
        $saleReturn->delete();
        return redirect()->route('saleReturn.list')->with('success', 'Sale return deleted successfully.');
    }

    /**
     * Change the status of a sale return. If processed, add returned quantity to the selected stock.
     */
    public function updateReturnStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,processed',
            'notes' => 'nullable|string|max:500'
        ]);

        $saleReturn = SaleReturn::with(['items'])->findOrFail($id);

        // Prevent re-processing
        if ($saleReturn->status === 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Sale return is already processed and cannot be updated.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $saleReturn->status;
            $newStatus = $request->status;
            $updateData = ['status' => $newStatus];

            // Add notes if provided
            if ($request->filled('notes')) {
                $currentNotes = $saleReturn->notes ? $saleReturn->notes . "\n" : "";
                $updateData['notes'] = $currentNotes . "[" . now()->format('Y-m-d H:i:s') . "] Status changed to " . ucfirst($newStatus) . ": " . $request->notes;
            }

            $saleReturn->update($updateData);

            // If status is being processed, adjust stock (add returned qty)
            if ($newStatus === 'processed') {
                foreach ($saleReturn->items as $item) {
                    $this->addStockForReturnItem($saleReturn, $item);
                }
            }

            DB::commit();

            $statusMessage = match($newStatus) {
                'approved' => 'Sale return has been approved successfully.',
                'rejected' => 'Sale return has been rejected.',
                'processed' => 'Sale return has been processed and stock has been updated.',
                default => 'Sale return status has been updated.'
            };

            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'data' => [
                    'id' => $saleReturn->id,
                    'status' => $saleReturn->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sale return status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add returned quantity to the selected stock (branch, warehouse, or employee)
     */
    private function addStockForReturnItem($saleReturn, $item)
    {
        $qty = $item->returned_qty;
        $productId = $item->product_id;
        $variationId = $item->variation_id ?? null;
        $toType = $saleReturn->return_to_type;
        $toId = $saleReturn->return_to_id;

        switch ($toType) {
            case 'branch':
                if ($variationId) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('branch_id', $toId)
                        ->whereNull('warehouse_id')
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'branch_id' => $toId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                } else {
                    $stock = \App\Models\BranchProductStock::where('branch_id', $toId)
                        ->where('product_id', $productId)
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\BranchProductStock::create([
                            'branch_id' => $toId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                }
                break;
            case 'warehouse':
                if ($variationId) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('warehouse_id', $toId)
                        ->whereNull('branch_id')
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'warehouse_id' => $toId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                } else {
                    $stock = \App\Models\WarehouseProductStock::where('warehouse_id', $toId)
                        ->where('product_id', $productId)
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\WarehouseProductStock::create([
                            'warehouse_id' => $toId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                }
                break;
            case 'employee':
                $stock = \App\Models\EmployeeProductStock::where('employee_id', $toId)
                    ->where('product_id', $productId)
                    ->first();
                if ($stock) {
                    $stock->increment('quantity', $qty);
                } else {
                    \App\Models\EmployeeProductStock::create([
                        'employee_id' => $toId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'issued_by' => auth()->id()
                    ]);
                }
                break;
            default:
                throw new \Exception("Invalid return_to_type: {$toType}");
        }
    }
} 