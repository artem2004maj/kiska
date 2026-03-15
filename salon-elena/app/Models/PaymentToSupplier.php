<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentToSupplier extends Model
{
    protected $fillable = [
        'amount',
        'payment_date',
        'status',
        'contract_id',
        'receipt_id',
    ];

    public function supplierContract()
    {
        return $this->belongsTo(SupplierContract::class, 'contract_id');
    }

    public function materialReceipt()
    {
        return $this->belongsTo(MaterialReceipt::class, 'receipt_id');
    }
}