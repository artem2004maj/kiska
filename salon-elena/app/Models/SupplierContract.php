<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierContract extends Model
{
    protected $table = 'supplier_contracts';
    protected $primaryKey = 'contract_id';

    protected $fillable = [
        'number',
        'date',
        'status',
        'valid_from',
        'valid_to',
        'supplier_id',
    ];

    // Статусы заказов
    const STATUS_PENDING = 0;      // Не подтвержден (ожидает подтверждения)
    const STATUS_CONFIRMED = 1;    // Подтвержден (в пути)
    const STATUS_RECEIVED = 2;     // Получен на склад
    const STATUS_CANCELLED = 3;    // Отменен

    protected $casts = [
        'date' => 'date',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function materialReceipts()
    {
        return $this->hasMany(MaterialReceipt::class, 'contract_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentToSupplier::class, 'contract_id');
    }

    // Вспомогательные методы
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isReceived()
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Не подтвержден',
            self::STATUS_CONFIRMED => 'В пути',
            self::STATUS_RECEIVED => 'Получен',
            self::STATUS_CANCELLED => 'Отменен',
            default => 'Неизвестно',
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_RECEIVED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }
}