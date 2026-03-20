<?php
// app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'service_id';

    protected $fillable = [
        'service_name',
        'service_category',
        'default_price',
        'is_active',
    ];

    /**
     * Связь с материалами через промежуточную таблицу service_material
     */
    public function materials()
    {
        return $this->belongsToMany(
            Material::class,
            'service_material',        // Имя промежуточной таблицы
            'service_id',               // Внешний ключ текущей модели
            'material_id'               // Внешний ключ связываемой модели
        )->withPivot('quantity', 'is_required')
         ->withTimestamps();
    }

    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class, 'service_id');
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class, 'service_id');
    }

    public function doctors()
    {
        return $this->belongsToMany(Employee::class, 'doctor_services', 'service_id', 'doctor_id')
                    ->withTimestamps();
    }
}