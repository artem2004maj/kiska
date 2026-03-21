<?php
// app/Models/Appointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    // ========== КОНСТАНТЫ СТАТУСОВ ==========
    const STATUS_PENDING = 0;      // Запланирован (ожидает подтверждения)
    const STATUS_CONFIRMED = 1;    // Подтвержден
    const STATUS_COMPLETED = 2;    // Завершен
    const STATUS_CANCELLED = 3;    // Отменен
    
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

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'appointment_id');
    }

    /**
     * Проверить, ожидает ли запись подтверждения
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Проверить, подтверждена ли запись
     */
    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Проверить, завершена ли запись
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Проверить, отменена ли запись
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Получить текстовое представление статуса
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Ожидает подтверждения',
            self::STATUS_CONFIRMED => 'Подтвержден',
            self::STATUS_COMPLETED => 'Завершен',
            self::STATUS_CANCELLED => 'Отменен',
            default => 'Неизвестно',
        };
    }

    /**
     * Получить цветовую метку статуса для CSS
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_COMPLETED => 'gray',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
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