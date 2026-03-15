<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentPlan extends Model
{
    protected $fillable = [
        'planned_quantity',
        'agreed_price',
        'is_completed',
        'record_id',
        'service_id',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}