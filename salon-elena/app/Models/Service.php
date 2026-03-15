<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'service_name',
        'service_category',
        'default_price',
        'is_active',
    ];

    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class);
    }
}