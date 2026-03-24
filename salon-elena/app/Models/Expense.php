<?php
// app/Models/Expense.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $primaryKey = 'expense_id';
    
    protected $fillable = [
        'type',
        'amount',
        'date',
        'description',
        'reference_id',
        'reference_type',
        'metadata',
        'is_confirmed',
        'confirmed_at',
    ];
    
    protected $casts = [
        'date' => 'date',
        'confirmed_at' => 'datetime',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'is_confirmed' => 'boolean',
    ];
    
    // Типы расходов
    const TYPE_SALARY = 'salary';
    const TYPE_SUPPLIER_ORDER = 'supplier_order';
    
    // Полиморфная связь с источником расхода
    public function source()
    {
        return $this->morphTo('source', 'reference_type', 'reference_id');
    }
    
    // Получить текст типа расхода
    public function getTypeTextAttribute()
    {
        return match($this->type) {
            self::TYPE_SALARY => 'Зарплата',
            self::TYPE_SUPPLIER_ORDER => 'Заказ поставщику',
            default => 'Другое',
        };
    }
    
    // Получить иконку для типа расхода
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            self::TYPE_SALARY => '💵',
            self::TYPE_SUPPLIER_ORDER => '🚚',
            default => '💰',
        };
    }
    
    // Связь с зарплатой
    public function salary()
    {
        return $this->belongsTo(Salary::class, 'reference_id', 'salary_id')
            ->where('reference_type', Salary::class);
    }
    
    // Связь с заказом поставщику
    public function supplierContract()
    {
        return $this->belongsTo(SupplierContract::class, 'reference_id', 'contract_id')
            ->where('reference_type', SupplierContract::class);
    }
}