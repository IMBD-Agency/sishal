<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductVariationStock;
use App\Models\WarehouseProductStock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function stocklist(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view stock')) {
            abort(403, 'Unauthorized action.');
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branches = $restrictedBranchId ? Branch::where('id', $restrictedBranchId)->get() : Branch::all();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::where('status', 'active')->get()->sortBy('full_path_name');
        $brands = \App\Models\Brand::where('status', 'active')->orderBy('name')->get();
        $seasons = \App\Models\Season::where('status', 'active')->orderBy('name')->get();
        $genders = \App\Models\Gender::all();
        $variationValues = \App\Models\VariationAttributeValue::whereHas('variations')->orderBy('value')->get();

        $query = Product::select('id', 'name', 'sku', 'style_number', 'price', 'cost', 'category_id', 'brand_id', 'season_id', 'gender_id', 'image', 'has_variations')
            ->with([
                'category:id,name', 
                'brand:id,name',
                'season:id,name',
                'gender:id,name'
            ]);

        $selectedBranchId = $restrictedBranchId ?: $request->branch_id;
        $selectedWarehouseId = $request->warehouse_id;
        $selectedVariationValueId = $request->variation_value_id;

        // Filter: Only show products that have been purchased at least once OR have current stock
        // This hides newly created products that haven't entered the inventory cycle yet.
        $query->where(function($q) {
            $q->whereHas('purchaseItems')
              ->orWhereHas('branchStock', function($sq) { $sq->where('quantity', '>', 0); })
              ->orWhereHas('warehouseStock', function($sq) { $sq->where('quantity', '>', 0); })
              ->orWhereHas('variationStocks', function($sq) { $sq->where('quantity', '>', 0); });
        });

        if ($selectedVariationValueId) {
            $query->whereHas('variations.attributeValues', function($q) use ($selectedVariationValueId) {
                $q->where('variation_attribute_values.id', $selectedVariationValueId);
            });
        }

        // Restriction Logic: If user is at a branch, they only see their branch and all warehouses
        $query->withSum(['branchStock as simple_branch_stock' => function($q) use ($selectedBranchId, $selectedWarehouseId, $restrictedBranchId) {
            if ($selectedBranchId) { 
                $q->where('branch_id', $selectedBranchId); 
            } elseif ($restrictedBranchId) {
                $q->where('branch_id', $restrictedBranchId);
            } elseif ($selectedWarehouseId) {
                $q->whereRaw('1=0'); 
            }
        }], 'quantity');

        $query->withSum(['warehouseStock as simple_warehouse_stock' => function($q) use ($selectedWarehouseId, $selectedBranchId, $restrictedBranchId) {
            if ($restrictedBranchId) {
                $q->whereRaw('1=0'); // Branch employees cannot see warehouse stock here
            } elseif ($selectedWarehouseId) { 
                $q->where('warehouse_id', $selectedWarehouseId); 
            }
            // For global users, if they don't select a warehouse, it includes all warehouse stock.
        }], 'quantity');

        $query->withSum(['variationStocks as var_stock' => function($q) use ($selectedBranchId, $selectedWarehouseId, $restrictedBranchId, $selectedVariationValueId) {
            if ($selectedVariationValueId) {
                $q->whereHas('variation.attributeValues', function($sq) use ($selectedVariationValueId) {
                    $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                });
            }
            if ($restrictedBranchId) {
                // Branch employees ONLY see their branch variations
                $q->where('branch_id', $restrictedBranchId);
            } elseif ($selectedBranchId && $selectedWarehouseId) {
                $q->where(function($sq) use ($selectedBranchId, $selectedWarehouseId) {
                    $sq->where('branch_id', $selectedBranchId)->orWhere('warehouse_id', $selectedWarehouseId);
                });
            } elseif ($selectedBranchId) { 
                $q->where(function($sq) use ($selectedBranchId) {
                    $sq->where('branch_id', $selectedBranchId)->orWhereNotNull('warehouse_id');
                });
            } elseif ($selectedWarehouseId) { 
                $q->where('warehouse_id', $selectedWarehouseId); 
            }
        }], 'quantity');

        // Filter by product name or SKU/Style Number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%")
                  ->orWhere('style_number', 'like', "%$search%");
            });
        }

        // Product Attribute Filters
        if ($request->filled('category_id')) { $query->where('category_id', $request->category_id); }
        if ($request->filled('brand_id')) { $query->where('brand_id', $request->brand_id); }
        if ($request->filled('season_id')) { $query->where('season_id', $request->season_id); }
        if ($request->filled('gender_id')) { $query->where('gender_id', $request->gender_id); }

        // Quick Month/Year Filters
        if ($request->filled('filter_year')) {
            $year = $request->filter_year;
            $month = $request->filled('filter_month') ? str_pad($request->filter_month, 2, '0', STR_PAD_LEFT) : null;
            
            if ($month) {
                $startDate = "$year-$month-01";
                $endDate = date("Y-m-t", strtotime($startDate));
                $request->merge(['start_date' => $startDate, 'end_date' => $endDate]);
            } else {
                $request->merge(['start_date' => "$year-01-01", 'end_date' => "$year-12-31"]);
            }
        }

        // Apply Date Filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort by Stock Quantity
        if ($request->filled('sort')) {
            $direction = $request->sort == 'high_to_low' ? 'DESC' : 'ASC';
            $query->orderByRaw("
                (CASE 
                    WHEN has_variations = 1 THEN COALESCE(var_stock, 0) 
                    ELSE COALESCE(simple_branch_stock, 0) + COALESCE(simple_warehouse_stock, 0) 
                END) $direction
            ");
        } else {
            $query->latest();
        }

        // Ultimate Big Data Optimization: Calculate totals in the database
        $totals = \DB::table(\DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query->getQuery())
            ->selectRaw("
                SUM(CASE WHEN has_variations = 1 THEN var_stock ELSE COALESCE(simple_branch_stock, 0) + COALESCE(simple_warehouse_stock, 0) END) as total_qty,
                SUM((CASE WHEN has_variations = 1 THEN var_stock ELSE COALESCE(simple_branch_stock, 0) + COALESCE(simple_warehouse_stock, 0) END) * cost) as total_value,
                SUM((CASE WHEN has_variations = 1 THEN var_stock ELSE COALESCE(simple_branch_stock, 0) + COALESCE(simple_warehouse_stock, 0) END) * price) as total_revenue
            ")
            ->first();

        $totalStockQty = $totals->total_qty ?? 0;
        $totalStockValue = $totals->total_value ?? 0;
        $totalStockRevenue = $totals->total_revenue ?? 0;

        // Pagination
        $perPage = (int) $request->get('per_page', 100);
        $productStocks = $query->paginate($perPage)->appends($request->except('page'));

        // Load breakdown relations ONLY for the 20 items on the current page
        $productStocks->load([
            'branchStock' => function($q) use ($restrictedBranchId, $selectedBranchId) {
                $activeBranch = $selectedBranchId ?: $restrictedBranchId;
                if ($activeBranch) $q->where('branch_id', $activeBranch);
                $q->with('branch:id,name');
            },
            'warehouseStock' => function($q) use ($restrictedBranchId) {
                if ($restrictedBranchId) {
                    $q->whereRaw('1=0');
                }
                $q->with('warehouse:id,name');
            }, 
            'variations' => function($q) use ($selectedVariationValueId) {
                if ($selectedVariationValueId) {
                    $q->whereHas('attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }
                $q->with('attributeValues');
            },
            'variations.stocks' => function($q) use ($restrictedBranchId, $selectedBranchId) {
                if ($restrictedBranchId) {
                    $q->where('branch_id', $restrictedBranchId);
                } else {
                    $activeBranch = $selectedBranchId;
                    if ($activeBranch) {
                        $q->where(function($sq) use ($activeBranch) {
                            $sq->where('branch_id', $activeBranch)->orWhereNotNull('warehouse_id');
                        });
                    }
                }
                $q->with(['branch:id,name', 'warehouse:id,name']);
            },
            'purchaseItems' => function($q) use ($selectedBranchId, $restrictedBranchId, $request, $selectedVariationValueId) {
                if ($request->filled('start_date')) $q->whereDate('created_at', '>=', $request->start_date);
                if ($request->filled('end_date')) $q->whereDate('created_at', '<=', $request->end_date);
                if (!$request->filled('start_date') && !$request->filled('end_date')) $q->whereYear('created_at', date('Y'));
                
                if ($selectedVariationValueId) {
                    $q->whereHas('variation.attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }

                $q->select('id', 'purchase_id', 'product_id', 'variation_id', 'quantity');
                $activeBranch = $selectedBranchId ?: $restrictedBranchId;
                if ($activeBranch) {
                    $q->whereHas('purchase', function($sq) use ($activeBranch) {
                        $sq->where('ship_location_type', 'branch')
                           ->where('location_id', $activeBranch);
                    });
                }
            },
            'saleItems' => function($q) use ($selectedBranchId, $restrictedBranchId, $request, $selectedVariationValueId) {
                if ($request->filled('start_date')) $q->whereDate('created_at', '>=', $request->start_date);
                if ($request->filled('end_date')) $q->whereDate('created_at', '<=', $request->end_date);
                if (!$request->filled('start_date') && !$request->filled('end_date')) $q->whereYear('created_at', date('Y'));

                if ($selectedVariationValueId) {
                    $q->whereHas('variation.attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }

                $q->select('id', 'pos_sale_id', 'product_id', 'variation_id', 'quantity');
                $activeBranch = $selectedBranchId ?: $restrictedBranchId;
                if ($activeBranch) {
                    $q->whereHas('pos', function($sq) use ($activeBranch) {
                        $sq->where('branch_id', $activeBranch);
                    });
                }
            },
            'invoiceItems' => function($q) use ($request, $selectedVariationValueId) {
                if ($request->filled('start_date')) $q->whereDate('created_at', '>=', $request->start_date);
                if ($request->filled('end_date')) $q->whereDate('created_at', '<=', $request->end_date);
                if (!$request->filled('start_date') && !$request->filled('end_date')) $q->whereYear('created_at', date('Y'));

                if ($selectedVariationValueId) {
                    $q->whereHas('variation.attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }

                $q->select('id', 'invoice_id', 'product_id', 'variation_id', 'quantity');
            }
        ]);
        
        // Only pass branch/warehouse selections based on permissions
        if ($restrictedBranchId) {
            // Branches already limited above
            // Don't empty warehouses if they should see it, but maybe limit the filter?
            // Actually, if they are restricted to a branch, the 'Branch' filter should probably be fixed or hidden.
        }

        $isDateFiltered = false;
        if ($request->filled('filter_month') || ($request->filled('filter_year') && $request->filter_year != date('Y'))) {
            $isDateFiltered = true;
        } elseif (!$request->filled('filter_year') && ($request->filled('start_date') || $request->filled('end_date'))) {
            $isDateFiltered = true;
        }

        if ($request->ajax()) {
            return view('erp.productStock.partials.table', compact(
                'productStocks', 'totalStockQty', 'totalStockValue', 'totalStockRevenue', 'isDateFiltered'
            ))->render();
        }

        return view('erp.productStock.productStockList', compact(
            'productStocks', 'branches', 'warehouses', 'categories', 'brands', 'seasons', 'genders', 'variationValues',
            'totalStockQty', 'totalStockValue', 'totalStockRevenue', 'selectedBranchId', 'selectedWarehouseId', 'restrictedBranchId', 'isDateFiltered'
        ));
    }

    public function exportStockExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view stock')) {
            abort(403, 'Unauthorized action.');
        }
        $products = $this->getStockQuery($request)->get();
        // Simplified Excel export using similar logic to adjustments
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $selectedVariationValueId = $request->variation_value_id;
        $variationValue = $selectedVariationValueId ? \App\Models\VariationAttributeValue::find($selectedVariationValueId) : null;
        $filterNote = $variationValue ? " (Filtered: Size/Variation {$variationValue->value})" : "";

        $headers = ['Name', 'SKU / Style', 'Category', 'Brand', 'Season', 'Gender', 'Size Breakdown', 'Bought', 'Sold', 'Cost', 'Price', 'Total Stock', 'Stock Value'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header . ($key >= 7 ? $filterNote : ''));
            $sheet->getStyle(chr(65 + $key) . '1')->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($products as $product) {
            $totalStock = 0;
            $sizes = [];
            $displayCost = $product->cost;
            $displayPrice = $product->price;
            
            // Current Stock Calculation
            if ($product->has_variations) {
                foreach($product->variations as $v) { 
                    $qty = $v->stocks->sum('quantity');
                    $totalStock += $qty;
                    if($qty > 0 || $v->stocks->count() > 0) {
                        $sizeName = $v->attributeValues->pluck('value')->implode(', ');
                        $sizes[] = "$sizeName: $qty";
                    }
                    // If we have a variation filter, use the first matching variation's cost/price
                    if ($selectedVariationValueId) {
                        if ($v->cost > 0) $displayCost = $v->cost;
                        if ($v->price > 0) $displayPrice = $v->price;
                    }
                }
            } else {
                $totalStock = $product->branchStock->sum('quantity') + $product->warehouseStock->sum('quantity');
            }

            // Purchased / Sold Calculation
            $totalPurchased = $product->purchaseItems->sum('quantity');
            $totalSold = $product->saleItems->sum('quantity') + $product->invoiceItems->sum('quantity');
            
            $sizeBreakdown = implode(', ', $sizes);
            
            $sheet->setCellValue('A' . $row, $product->name);
            $sheet->setCellValue('B' . $row, $product->style_number ?? $product->sku);
            $sheet->setCellValue('C' . $row, $product->category->name ?? '-');
            $sheet->setCellValue('D' . $row, $product->brand->name ?? '-');
            $sheet->setCellValue('E' . $row, $product->season->name ?? 'ALL');
            $sheet->setCellValue('F' . $row, $product->gender->name ?? 'ALL');
            $sheet->setCellValue('G' . $row, $sizeBreakdown ?: ($product->has_variations ? 'Out of Stock' : 'No Variations'));
            $sheet->setCellValue('H' . $row, $totalPurchased);
            $sheet->setCellValue('I' . $row, $totalSold);
            $sheet->setCellValue('J' . $row, $displayCost);
            $sheet->setCellValue('K' . $row, $displayPrice);
            $sheet->setCellValue('L' . $row, $totalStock);
            $sheet->setCellValue('M' . $row, $totalStock * $displayCost);
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'inventory_report_' . date('Y-m-d') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportStockPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view stock')) {
            abort(403, 'Unauthorized action.');
        }
        $products = $this->getStockQuery($request)->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.productStock.stock-report-pdf', compact('products'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('inventory_report_' . date('Y-m-d') . '.pdf');
    }

    private function getStockQuery(Request $request)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        $selectedBranchId = $restrictedBranchId ?: $request->branch_id;
        $selectedWarehouseId = $request->warehouse_id;
        $selectedVariationValueId = $request->variation_value_id;

        // Apply Month/Year merging logic to the request object if present
        if ($request->filled('filter_year')) {
            $year = $request->filter_year;
            $month = $request->filled('filter_month') ? str_pad($request->filter_month, 2, '0', STR_PAD_LEFT) : null;
            if ($month) {
                $request->merge(['start_date' => "$year-$month-01", 'end_date' => date("Y-m-t", strtotime("$year-$month-01"))]);
            } else {
                $request->merge(['start_date' => "$year-01-01", 'end_date' => "$year-12-31"]);
            }
        }

        $query = Product::with([
            'category:id,name', 
            'brand:id,name',
            'season:id,name',
            'gender:id,name',
            'branchStock' => function($q) use ($selectedBranchId, $restrictedBranchId) {
                $activeBranch = $selectedBranchId ?: $restrictedBranchId;
                if ($activeBranch) $q->where('branch_id', $activeBranch);
                $q->with('branch:id,name');
            },
            'warehouseStock' => function($q) use ($restrictedBranchId) {
                if ($restrictedBranchId) $q->whereRaw('1=0');
                $q->with('warehouse:id,name');
            },
            'variations' => function($q) use ($selectedVariationValueId) {
                if ($selectedVariationValueId) {
                    $q->whereHas('attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }
                $q->with('attributeValues');
            },
            'variations.stocks' => function($q) use ($selectedBranchId, $restrictedBranchId) {
                $activeBranch = $selectedBranchId ?: $restrictedBranchId;
                if ($activeBranch) {
                    $q->where(function($sq) use ($activeBranch) {
                        $sq->where('branch_id', $activeBranch)->orWhereNotNull('warehouse_id');
                    });
                }
                $q->with(['branch:id,name', 'warehouse:id,name']);
            },
            'purchaseItems' => function($q) use ($selectedBranchId, $restrictedBranchId, $request, $selectedVariationValueId) {
                if ($request->filled('start_date')) $q->whereDate('created_at', '>=', $request->start_date);
                if ($request->filled('end_date')) $q->whereDate('created_at', '<=', $request->end_date);
                if (!$request->filled('start_date') && !$request->filled('end_date')) $q->whereYear('created_at', date('Y'));
                
                if ($selectedVariationValueId) {
                    $q->whereHas('variation.attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }

                $activeBranch = $selectedBranchId ?: $restrictedBranchId;
                if ($activeBranch) {
                    $q->whereHas('purchase', function($sq) use ($activeBranch) {
                        $sq->where('ship_location_type', 'branch')->where('location_id', $activeBranch);
                    });
                }
            },
            'saleItems' => function($q) use ($selectedBranchId, $restrictedBranchId, $request, $selectedVariationValueId) {
                if ($request->filled('start_date')) $q->whereDate('created_at', '>=', $request->start_date);
                if ($request->filled('end_date')) $q->whereDate('created_at', '<=', $request->end_date);
                if (!$request->filled('start_date') && !$request->filled('end_date')) $q->whereYear('created_at', date('Y'));

                if ($selectedVariationValueId) {
                    $q->whereHas('variation.attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }

                $activeBranch = $selectedBranchId ?: $restrictedBranchId;
                if ($activeBranch) {
                    $q->whereHas('pos', function($sq) use ($activeBranch) {
                        $sq->where('branch_id', $activeBranch);
                    });
                }
            },
            'invoiceItems' => function($q) use ($request, $selectedVariationValueId) {
                if ($request->filled('start_date')) $q->whereDate('created_at', '>=', $request->start_date);
                if ($request->filled('end_date')) $q->whereDate('created_at', '<=', $request->end_date);
                if (!$request->filled('start_date') && !$request->filled('end_date')) $q->whereYear('created_at', date('Y'));

                if ($selectedVariationValueId) {
                    $q->whereHas('variation.attributeValues', function($sq) use ($selectedVariationValueId) {
                        $sq->where('variation_attribute_values.id', $selectedVariationValueId);
                    });
                }
            }
        ]);

        if ($selectedVariationValueId) {
            $query->whereHas('variations.attributeValues', function($q) use ($selectedVariationValueId) {
                $q->where('variation_attribute_values.id', $selectedVariationValueId);
            });
        }

        // Filter: Only show products that have been purchased at least once OR have current stock
        $query->where(function($q) {
            $q->whereHas('purchaseItems')
              ->orWhereHas('branchStock', function($sq) { $sq->where('quantity', '>', 0); })
              ->orWhereHas('warehouseStock', function($sq) { $sq->where('quantity', '>', 0); })
              ->orWhereHas('variationStocks', function($sq) { $sq->where('quantity', '>', 0); });
        });
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('sku', 'like', "%$search%")->orWhere('style_number', 'like', "%$search%");
            });
        }

        if ($request->filled('category_id')) { $query->where('category_id', $request->category_id); }
        if ($request->filled('brand_id')) { $query->where('brand_id', $request->brand_id); }
        if ($request->filled('season_id')) { $query->where('season_id', $request->season_id); }
        if ($request->filled('gender_id')) { $query->where('gender_id', $request->gender_id); }
        
        if ($request->filled('start_date')) { $query->whereDate('created_at', '>=', $request->start_date); }
        if ($request->filled('end_date')) { $query->whereDate('created_at', '<=', $request->end_date); }

        return $query;
    }

    public function addStockToBranches(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('adjust stock')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branches' => 'required|array',
            'branches.*' => 'exists:branches,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:1',
        ]);

        $productId = $request->product_id;
        $branches = $request->branches;
        $quantities = $request->quantities;

        \Log::alert($request->all());

        foreach ($branches as $i => $branchId) {
            $quantity = $quantities[$i];
            $stock = \App\Models\BranchProductStock::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->first();
            if ($stock) {
                $newQuantity = $stock->quantity + $quantity;
                $stock->update([
                    'quantity' => $newQuantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            } else {
                \App\Models\BranchProductStock::create([
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }

        \App\Services\CacheService::clearProductCaches($productId);

        return response()->json(['success' => true, 'message' => 'Stock added to branches successfully.']);
    }

    public function addStockToWarehouses(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('adjust stock')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouses' => 'required|array',
            'warehouses.*' => 'exists:warehouses,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:1',
        ]);

        $productId = $request->product_id;
        $warehouses = $request->warehouses;
        $quantities = $request->quantities;

        foreach ($warehouses as $i => $warehouseId) {
            $quantity = $quantities[$i];
            $stock = \App\Models\WarehouseProductStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();
            if ($stock) {
                $newQuantity = $stock->quantity + $quantity;
                $stock->update([
                    'quantity' => $newQuantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            } else {
                \App\Models\WarehouseProductStock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }

        \App\Services\CacheService::clearProductCaches($productId);

        return response()->json(['success' => true, 'message' => 'Stock added to warehouses successfully.']);
    }

    public function adjustStock(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('adjust stock')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'location_type' => 'required|in:branch,warehouse',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:stock_in,stock_out',
            'quantity' => 'required|numeric|min:1',
        ]);

        // Validate location ID based on location type
        if ($request->location_type == 'branch') {
            $request->validate(['branch_id' => 'required|exists:branches,id']);
        } else {
            $request->validate(['warehouse_id' => 'required|exists:warehouses,id']);
        }

        // If a variation_id is provided, adjust variation stock; otherwise fall back to product-level stock
        $isVariation = $request->filled('variation_id');

        if($request->location_type == 'branch')
        {
            if ($isVariation) {
                $stock = ProductVariationStock::where('variation_id', $request->variation_id)
                    ->where('branch_id', $request->branch_id)
                    ->whereNull('warehouse_id')
                    ->first();

                if ($stock) {
                    if($request->type == 'stock_in') {
                        $stock->quantity += $request->quantity;
                    } else {
                        if($stock->quantity >= $request->quantity){
                            $stock->quantity -= $request->quantity;
                        } else {
                            return response()->json(['success' => false, 'message' => 'Insufficient variation stock'], 400);
                        }
                    }
                    $stock->updated_by = auth()->id() ?? 1;
                    $stock->last_updated_at = now();
                    $stock->save();
                } else {
                    if($request->type == 'stock_in') {
                        ProductVariationStock::create([
                            'variation_id' => $request->variation_id,
                            'branch_id' => $request->branch_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No variation stock to decrement for this branch.'], 400);
                    }
                }
            } else {
                $branchStock = BranchProductStock::where('branch_id', $request->branch_id)->where('product_id', $request->product_id)->first();
                if ($branchStock) {
                    if($request->type == 'stock_in')
                    {
                        $branchStock->quantity += $request->quantity;
                    }else{
                        if($branchStock->quantity > 0){
                            $branchStock->quantity -= $request->quantity;
                        }else{
                            return response()->json(['success' => false, 'message' => 'Stock is already empty'], 400);
                        }
                    }
                    $branchStock->save();
                } else {
                    if($request->type == 'stock_in') {
                        BranchProductStock::create([
                            'branch_id' => $request->branch_id,
                            'product_id' => $request->product_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No stock found for this branch and product. Cannot stock out.'], 400);
                    }
                }
            }
        } else {
            if ($isVariation) {
                $stock = ProductVariationStock::where('variation_id', $request->variation_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->whereNull('branch_id')
                    ->first();

                if ($stock) {
                    if($request->type == 'stock_in') {
                        $stock->quantity += $request->quantity;
                    } else {
                        if($stock->quantity >= $request->quantity){
                            $stock->quantity -= $request->quantity;
                        } else {
                            return response()->json(['success' => false, 'message' => 'Insufficient variation stock'], 400);
                        }
                    }
                    $stock->updated_by = auth()->id() ?? 1;
                    $stock->last_updated_at = now();
                    $stock->save();
                } else {
                    if($request->type == 'stock_in') {
                        ProductVariationStock::create([
                            'variation_id' => $request->variation_id,
                            'warehouse_id' => $request->warehouse_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No variation stock to decrement for this warehouse.'], 400);
                    }
                }
            } else {
                $warehouseStock = WarehouseProductStock::where('warehouse_id', $request->warehouse_id)->where('product_id', $request->product_id)->first();
                if ($warehouseStock) {
                    if($request->type == 'stock_in')
                    {
                        $warehouseStock->quantity += $request->quantity;
                    } else{
                        if($warehouseStock->quantity > 0)
                        {
                            $warehouseStock->quantity -= $request->quantity;
                        }else{
                            return response()->json(['success' => false, 'message' => 'Stock is already empty'], 400);
                        }
                    }
                    $warehouseStock->save();
                } else {
                    if($request->type == 'stock_in') {
                        WarehouseProductStock::create([
                            'warehouse_id' => $request->warehouse_id,
                            'product_id' => $request->product_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No stock found for this warehouse and product. Cannot stock out.'], 400);
                    }
                }
            }
        }
        
        \App\Services\CacheService::clearProductCaches($request->product_id);
        
        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }

    public function getCurrentStock(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view stock')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_type' => 'required|in:branch,warehouse',
        ]);

        // Validate location ID based on location type
        if ($request->location_type == 'branch') {
            $request->validate(['branch_id' => 'required|exists:branches,id']);
        } else {
            $request->validate(['warehouse_id' => 'required|exists:warehouses,id']);
        }

        $productId = $request->product_id;
        $variationId = $request->variation_id ?? null;
        $locationType = $request->location_type;
        $quantity = 0;

        if ($variationId) {
            // Get variation stock
            if ($locationType == 'branch') {
                $stock = ProductVariationStock::where('variation_id', $variationId)
                    ->where('branch_id', $request->branch_id)
                    ->whereNull('warehouse_id')
                    ->first();
            } else {
                $stock = ProductVariationStock::where('variation_id', $variationId)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->whereNull('branch_id')
                    ->first();
            }
            $quantity = $stock ? $stock->quantity : 0;
        } else {
            // Get product-level stock
            if ($locationType == 'branch') {
                $stock = BranchProductStock::where('product_id', $productId)
                    ->where('branch_id', $request->branch_id)
                    ->first();
            } else {
                $stock = WarehouseProductStock::where('product_id', $productId)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();
            }
            $quantity = $stock ? $stock->quantity : 0;
        }

        return response()->json([
            'success' => true,
            'quantity' => $quantity
        ]);
    }
    public function adjustmentCreate()
    {
        if (!auth()->user()->hasPermissionTo('adjust stock')) {
            abort(403, 'Unauthorized action.');
        }

        $branches = Branch::all();
        $warehouses = Warehouse::all();
        
        return view('erp.productStock.createAdjustment', compact('branches', 'warehouses'));
    }

    public function storeAdjustment(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('adjust stock')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric',
        ]);

        \DB::beginTransaction();
        try {
            $branchId = $request->branch_id;
            $userId = auth()->id() ?? 1;

            // Create adjustment header
            $adjustment = \App\Models\StockAdjustment::create([
                'adjustment_number' => 'ADJ-' . time() . rand(10, 99),
                'date' => now(),
                'branch_id' => $branchId,
                'notes' => $request->note,
                'created_by' => $userId,
            ]);

            foreach ($request->items as $itemData) {
                $productId = $itemData['product_id'];
                $variationId = $itemData['variation_id'] ?? null;
                $newQty = $itemData['quantity'];
                
                $oldQty = 0;
                
                if ($variationId) {
                    $stock = ProductVariationStock::where('variation_id', $variationId)
                        ->where('branch_id', $branchId)
                        ->whereNull('warehouse_id')
                        ->first();
                        
                    if ($stock) {
                        $oldQty = $stock->quantity;
                        $stock->quantity = $newQty;
                        $stock->updated_by = $userId;
                        $stock->last_updated_at = now();
                        $stock->save();
                    } else {
                        ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'branch_id' => $branchId,
                            'quantity' => $newQty,
                            'updated_by' => $userId,
                            'last_updated_at' => now(),
                        ]);
                    }
                } else {
                    $stock = BranchProductStock::where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->first();
                        
                    if ($stock) {
                        $oldQty = $stock->quantity;
                        $stock->quantity = $newQty;
                        $stock->updated_by = $userId;
                        $stock->last_updated_at = now();
                        $stock->save();
                    } else {
                        BranchProductStock::create([
                            'product_id' => $productId,
                            'branch_id' => $branchId,
                            'quantity' => $newQty,
                            'updated_by' => $userId,
                            'last_updated_at' => now(),
                        ]);
                    }
                }

                // Record item
                $adjustment->items()->create([
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'old_quantity' => $oldQty,
                    'new_quantity' => $newQty,
                ]);
            }

            foreach ($request->items as $itemData) {
                \App\Services\CacheService::clearProductCaches($itemData['product_id']);
            }

            \DB::commit();
            return redirect()->route('stock.adjustment.list')->with('success', 'Stock adjusted successfully and record saved!');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Stock Adjustment Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Adjustment failed: ' . $e->getMessage());
        }
    }

    public function adjustmentList(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view stock')) {
            abort(403, 'Unauthorized action.');
        }

        $query = \App\Models\StockAdjustmentItem::with(['adjustment.branch', 'adjustment.creator', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation']);

        // Reports Filter logic (applied to parent adjustment)
        $reportType = $request->get('report_type', 'yearly');
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('n'));
            $year = $request->get('year', date('Y'));
            $query->whereHas('adjustment', function($q) use ($month, $year) {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            });
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $query->whereHas('adjustment', function($q) use ($year) {
                $q->whereYear('date', $year);
            });
        } else {
            if ($request->filled('start_date')) {
                $query->whereHas('adjustment', function($q) use ($request) {
                    $q->where('date', '>=', $request->start_date);
                });
            }
            if ($request->filled('end_date')) {
                $query->whereHas('adjustment', function($q) use ($request) {
                    $q->where('date', '<=', $request->end_date);
                });
            }
        }

        // Parent Adjustment Filters
        if ($request->filled('adjustment_number')) {
            $query->whereHas('adjustment', function($q) use ($request) {
                $q->where('adjustment_number', 'like', '%' . $request->adjustment_number . '%');
            });
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $selectedBranchId = $restrictedBranchId ?: $request->branch_id;

        if ($selectedBranchId) {
            $query->whereHas('adjustment', function($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            });
        }

        // Item/Product Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('style_number', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%");
            });
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('style_number')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('style_number', 'like', '%' . $request->style_number . '%')
                  ->orWhere('sku', 'like', '%' . $request->style_number . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('brand_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }
        if ($request->filled('season_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('season_id', $request->season_id);
            });
        }
        if ($request->filled('gender_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('gender_id', $request->gender_id);
            });
        }

        $perPage = (int) $request->get('per_page', 50);
        $adjustments = $query->latest()->paginate($perPage)->appends($request->except('page'));

        if ($request->ajax()) {
            return view('erp.productStock.components.adjustmentTable', compact('adjustments'))->render();
        }

        $branches = Branch::all();
        $products = Product::orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->get();
        $brands = \App\Models\Brand::all();
        $seasons = \App\Models\Season::all();
        $genders = \App\Models\Gender::all();

        return view('erp.productStock.adjustmentList', compact(
            'adjustments', 'branches', 'products', 'categories', 'brands', 'seasons', 'genders', 'reportType'
        ));
    }

    public function exportAdjustmentExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view stock')) {
            abort(403, 'Unauthorized action.');
        }
        $items = $this->getAdjustmentQuery($request)->get();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['Serial No', 'Invoice', 'Date', 'Category', 'Brand', 'Season', 'Gender', 'Product Name', 'Style Number', 'Old Qty', 'New Qty', 'Diff', 'Adjusted By'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header);
        }
        
        $row = 2;
        foreach ($items as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->adjustment->adjustment_number);
            $sheet->setCellValue('C' . $row, $item->adjustment->date);
            $sheet->setCellValue('D' . $row, $item->product->category->name ?? '-');
            $sheet->setCellValue('E' . $row, $item->product->brand->name ?? '-');
            $sheet->setCellValue('F' . $row, $item->product->season->name ?? '-');
            $sheet->setCellValue('G' . $row, $item->product->gender->name ?? '-');
            $sheet->setCellValue('H' . $row, $item->product->name);
            $sheet->setCellValue('I' . $row, $item->product->style_number);
            $sheet->setCellValue('J' . $row, $item->old_quantity);
            $sheet->setCellValue('K' . $row, $item->new_quantity);
            $sheet->setCellValue('L' . $row, $item->new_quantity - $item->old_quantity);
            $sheet->setCellValue('M' . $row, $item->adjustment->creator->name ?? 'Admin');
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'stock_adjustments_' . date('Y-m-d') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);
        
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportAdjustmentPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view stock')) {
            abort(403, 'Unauthorized action.');
        }
        $adjustments = $this->getAdjustmentQuery($request)->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.productStock.adjustment-report-pdf', compact('adjustments'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('stock_adjustments_' . date('Y-m-d') . '.pdf');
    }

    private function getAdjustmentQuery(Request $request)
    {
        $query = \App\Models\StockAdjustmentItem::with(['adjustment.branch', 'adjustment.creator', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation']);

        if ($request->filled('report_type')) {
            $reportType = $request->report_type;
            if ($reportType == 'monthly') {
                $month = $request->get('month', date('n'));
                $year = $request->get('year', date('Y'));
                $query->whereHas('adjustment', function($q) use ($month, $year) {
                    $q->whereMonth('date', $month)->whereYear('date', $year);
                });
            } elseif ($reportType == 'yearly') {
                $year = $request->get('year', date('Y'));
                $query->whereHas('adjustment', function($q) use ($year) {
                    $q->whereYear('date', $year);
                });
            } else {
                if ($request->filled('start_date')) {
                    $query->whereHas('adjustment', function($q) use ($request) {
                        $q->where('date', '>=', $request->start_date);
                    });
                }
                if ($request->filled('end_date')) {
                    $query->whereHas('adjustment', function($q) use ($request) {
                        $q->where('date', '<=', $request->end_date);
                    });
                }
            }
        }

        if ($request->filled('adjustment_number')) {
            $query->whereHas('adjustment', function($q) use ($request) {
                $q->where('adjustment_number', 'like', '%' . $request->adjustment_number . '%');
            });
        }
        if ($request->filled('branch_id')) {
            $query->whereHas('adjustment', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('style_number')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('style_number', 'like', '%' . $request->style_number . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        return $query->latest();
    }
}
