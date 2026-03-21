<?php
// app/Models/Salary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $primaryKey = 'salary_id';
    
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'hours_worked',
        'hourly_rate',
        'total_amount',
        'is_paid',
        'payment_date',
        'calculation_data', // Добавляем поле для хранения деталей расчета
    ];

    protected $casts = [
        'payment_date' => 'date',
        'is_paid' => 'boolean',
        'calculation_data' => 'array', // Автоматически преобразуем JSON в массив
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    
    /**
     * Получить детали расчета
     */
    public function getCalculationDetailsAttribute()
    {
        return $this->calculation_data ?: [];
    }
}