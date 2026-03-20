<?php
// app/Models/DoctorSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id', 'employee_id');
    }

    /**
     * Получить название дня недели
     */
    public function getDayNameAttribute()
    {
        $days = [
            0 => 'Воскресенье',
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
        ];
        return $days[$this->day_of_week] ?? 'Неизвестно';
    }
}