<?php
// app/Models/Appointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'date',
        'status',
        'employee_id',
        'client_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class, 'appointment_id');
    }

    public function materials()
    {
        return $this->belongsToMany(
            Material::class,
            'appointment_materials',
            'appointment_id',
            'material_id'
        )->withPivot('quantity_used', 'cost_price', 'notes')
         ->withTimestamps();
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class, 'appointment_id');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id');
    }
}