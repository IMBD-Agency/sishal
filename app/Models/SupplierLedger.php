<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierLedger extends Model
{
    protected $fillable = [
        'supplier_id',
        'transactionable_type',
        'transactionable_id',
        'date',
        'description',
        'debit',
        'credit',
        'balance',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function recordTransaction($supplierId, $type, $amount, $description, $date, $transactionable = null)
    {
        $lastEntry = self::where('supplier_id', $supplierId)->latest('id')->first();
        $previousBalance = $lastEntry ? $lastEntry->balance : 0;

        $debit = 0;
        $credit = 0;

        if ($type == 'debit') {
            $debit = $amount;
            $balance = $previousBalance - $amount; // Paying reduces debt
        } else {
            $credit = $amount;
            $balance = $previousBalance + $amount; // Purchasing increases debt
        }

        return self::create([
            'supplier_id' => $supplierId,
            'transactionable_type' => $transactionable ? get_class($transactionable) : null,
            'transactionable_id' => $transactionable ? $transactionable->id : null,
            'date' => $date,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $balance,
            'created_by' => auth()->id() ?? 1, // Fallback for bulk imports or console
        ]);
    }
}
