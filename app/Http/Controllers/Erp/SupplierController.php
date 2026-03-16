<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        $query = $this->buildSupplierQuery($request);

        if ($request->export === 'excel') {
            return $this->exportExcel($query->get(['*']));
        }

        if ($request->export === 'pdf') {
            return $this->exportPdf($query->get(['*']));
        }

        $suppliers = $query->latest('id')->paginate(20)->appends($request->all());
        
        // Get unique cities and countries for filters
        $cities = Supplier::whereNotNull('city', 'and')->distinct()->pluck('city')->sort();
        $countries = Supplier::whereNotNull('country', 'and')->distinct()->pluck('country')->sort();

        return view('erp.suppliers.index', compact('suppliers', 'cities', 'countries'));
    }

    private function buildSupplierQuery(Request $request)
    {
        $query = Supplier::query();
        
        // Search by multiple fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%", 'and')
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('tax_number', 'LIKE', "%{$search}%");
            });
        }

        // Dropdown Filters
        if ($request->filled('city')) {
            $query->where('city', '=', $request->city, 'and');
        }
        if ($request->filled('country')) {
            $query->where('country', '=', $request->country, 'and');
        }

        return $query;
    }

    public function exportExcel($suppliers)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['SN', 'ID', 'Name', 'Email', 'Phone', 'Company', 'Tax Number', 'Balance', 'City', 'Country'];
        foreach($headers as $k => $h) { 
            $sheet->setCellValue(chr(65+$k).'1', $h); 
            $sheet->getStyle(chr(65+$k).'1')->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach($suppliers as $index => $s) {
            $balance = $s->balance;
            $balanceText = number_format(abs($balance), 2);
            if ($balance > 0) $balanceText .= ' (DUE)';
            elseif ($balance < 0) $balanceText .= ' (ADV)';

            $sheet->setCellValue('A'.$row, $index + 1);
            $sheet->setCellValue('B'.$row, 'SUP-'.str_pad($s->id, 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue('C'.$row, $s->name);
            $sheet->setCellValue('D'.$row, $s->email ?? '-');
            $sheet->setCellValue('E'.$row, $s->phone);
            $sheet->setCellValue('F'.$row, $s->company_name ?? '-');
            $sheet->setCellValue('G'.$row, $s->tax_number ?? '-');
            $sheet->setCellValue('H'.$row, $balanceText);
            $sheet->setCellValue('I'.$row, $s->city ?? '-');
            $sheet->setCellValue('J'.$row, $s->country ?? '-');
            $row++;
        }

        foreach(range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'supplier_list_' . date('Ymd_His') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportPdf($suppliers)
    {
        $pdf = Pdf::loadView('erp.suppliers.supplier-export-pdf', compact('suppliers'));
        return $pdf->download('supplier_report_' . date('Y-m-d') . '.pdf');
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage suppliers')) {
            abort(403, 'Unauthorized action.');
        }
        return view('erp.suppliers.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage suppliers')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(string $id)
    {
        if (!auth()->user()->hasPermissionTo('view suppliers')) {
            abort(403, 'Unauthorized action.');
        }
        $supplier = Supplier::with('purchases.items')->findOrFail($id);
        return view('erp.suppliers.show', compact('supplier'));
    }

    public function edit(string $id)
    {
        if (!auth()->user()->hasPermissionTo('manage suppliers')) {
            abort(403, 'Unauthorized action.');
        }
        $supplier = Supplier::findOrFail($id);
        return view('erp.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->hasPermissionTo('manage suppliers')) {
            abort(403, 'Unauthorized action.');
        }
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->hasPermissionTo('manage suppliers')) {
            abort(403, 'Unauthorized action.');
        }
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    public function ledger(string $id)
    {
        if (!auth()->user()->hasPermissionTo('view suppliers')) {
            abort(403, 'Unauthorized action.');
        }
        $supplier = Supplier::findOrFail($id);
        $entries = $supplier->ledgerEntries()->paginate(50);
        return view('erp.suppliers.ledger', compact('supplier', 'entries'));
    }
}
