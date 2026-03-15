<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $fillable = [
        'visit_date',
        'anamnesis',
        'diagnosis',
        'contraindications',
        'employee_id',
        'client_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class, 'record_id');
    }
}