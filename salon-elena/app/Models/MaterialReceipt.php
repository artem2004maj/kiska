<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialReceipt extends Model
{
    protected $table = 'material_receipts';
    protected $primaryKey = 'receipt_id';

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

    // Статусы поступлений
    const STATUS_PENDING = 0;   // Ожидает поступления
    const STATUS_RECEIVED = 1;  // Получен

    protected $casts = [
        'receipt_date' => 'date',
        'expiry_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }

    // Основное отношение к договору
    public function supplierContract()
    {
        return $this->belongsTo(SupplierContract::class, 'contract_id');
    }

    // Алиас для обратной совместимости
    public function contract()
    {
        return $this->supplierContract();
    }

    public function payments()
    {
        return $this->hasMany(PaymentToSupplier::class, 'receipt_id');
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReceived()
    {
        return $this->status === self::STATUS_RECEIVED;
    }
}