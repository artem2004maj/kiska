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
        'calculation_data',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'is_paid' => 'boolean',
        'calculation_data' => 'array',
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
    
    /**
     * Связь с расходом
     */
    public function expense()
    {
        return $this->morphOne(Expense::class, 'source', 'reference_type', 'reference_id');
    }
    
    /**
     * Создать запись о расходе при выплате зарплаты
     */
    public function createExpenseRecord()
    {
        // Проверяем, что зарплата выплачена и есть дата выплаты
        if ($this->is_paid && $this->payment_date) {
            // Проверяем, нет ли уже записи о расходе
            if ($this->expense) {
                return $this->expense;
            }
            
            // Получаем данные о сотруднике
            $employee = $this->employee;
            
            return Expense::create([
                'type' => Expense::TYPE_SALARY,
                'amount' => $this->total_amount,
                'date' => $this->payment_date,
                'description' => "Зарплата сотрудника: {$employee->employee_name} за {$this->month}/{$this->year}",
                'reference_id' => $this->salary_id,
                'reference_type' => Salary::class,
                'metadata' => [
                    'employee_id' => $this->employee_id,
                    'employee_name' => $employee->employee_name,
                    'month' => $this->month,
                    'year' => $this->year,
                    'hours_worked' => $this->hours_worked,
                    'hourly_rate' => $this->hourly_rate,
                    'calculation_details' => $this->calculation_details,
                ],
                'is_confirmed' => true,
                'confirmed_at' => $this->payment_date,
            ]);
        }
        
        return null;
    }
    
    /**
     * Переопределяем метод save для автоматического создания расхода
     */
    protected static function booted()
    {
        static::updated(function ($salary) {
            // Если статус изменился на выплаченный и появилась дата выплаты
            if ($salary->is_paid && $salary->payment_date) {
                $salary->createExpenseRecord();
            }
        });
    }
}