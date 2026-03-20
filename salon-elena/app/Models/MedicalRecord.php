<?php
// app/Models/MedicalRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $primaryKey = 'record_id';
    
    protected $fillable = [
        'visit_date',
        'anamnesis',
        'diagnosis',
        'contraindications',
        'employee_id',
        'client_id',
        'appointment_id', // ДОБАВЛЯЕМ связь с приемом
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class, 'record_id');
    }
}