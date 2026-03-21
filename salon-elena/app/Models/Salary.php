<?php
// app/Models/Salary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'hours_worked',
        'hourly_rate',
        'total_amount',
        'is_paid',
        'payment_date',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}