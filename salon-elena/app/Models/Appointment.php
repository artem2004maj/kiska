<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id'; // Добавить эту строку
    public $incrementing = true; // Если поле автоинкрементное
    protected $keyType = 'int'; // Тип ключа

    protected $fillable = [
        'date',
        'status',
        'employee_id',
        'client_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class, 'appointment_id', 'appointment_id');
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class, 'appointment_id', 'appointment_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'appointment_id', 'appointment_id');
    }
}