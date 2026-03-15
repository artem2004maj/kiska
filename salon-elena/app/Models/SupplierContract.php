<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierContract extends Model
{
    protected $fillable = [
        'number',
        'date',
        'status',
        'valid_from',
        'valid_to',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function materialReceipts()
    {
        return $this->hasMany(MaterialReceipt::class, 'contract_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentToSupplier::class, 'contract_id');
    }
}