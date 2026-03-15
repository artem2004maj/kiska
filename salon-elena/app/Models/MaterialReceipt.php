<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialReceipt extends Model
{
    protected $fillable = [
        'quantity',
        'price',
        'batch_number',
        'expiry_date',
        'receipt_date',
        'invoice_number',
        'status',
        'material_id',
        'contract_id',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function supplierContract()
    {
        return $this->belongsTo(SupplierContract::class, 'contract_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentToSupplier::class, 'receipt_id');
    }
}