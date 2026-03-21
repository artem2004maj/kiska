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
        'total_price',
        'employee_id',
        'client_id',
    ];

    protected $casts = [
        'date' => 'datetime',
        'total_price' => 'decimal:2',
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

    public function clientContract()
    {
        return $this->hasOne(ClientContract::class, 'appointment_id');
    }

    /**
     * Рассчитать итоговую стоимость приема
     */
    public function calculateTotalPrice()
    {
        $total = 0;
        
        // Стоимость услуг
        foreach ($this->providedServices as $service) {
            if ($service->service) {
                $total += $service->service->default_price * $service->quantity;
            }
        }
        
        // Стоимость материалов (из pivot таблицы appointment_materials)
        foreach ($this->materials as $material) {
            $price = $material->pivot->cost_price ?? $material->price_per_unit ?? 0;
            $total += $price * $material->pivot->quantity_used;
        }
        
        return $total;
    }
    
    /**
     * Получить название услуги (первой) для отображения
     */
    public function getServiceNameAttribute()
    {
        $service = $this->providedServices->first()?->service;
        return $service ? $service->service_name : null;
    }
    
    /**
     * Получить все услуги в виде строки
     */
    public function getServicesListAttribute()
    {
        if (!$this->providedServices || $this->providedServices->isEmpty()) {
            return 'Услуга не указана';
        }
        return $this->providedServices->map(function($ps) {
            return $ps->service ? $ps->service->service_name : null;
        })->filter()->join(', ');
    }
}