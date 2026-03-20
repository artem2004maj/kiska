<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvidedService extends Model
{
    protected $fillable = [
        'quantity',
        'service_date',
        'notes',
        'appointment_id',
        'service_id',
        'employee_id',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class, 'provided_id');
    }
}