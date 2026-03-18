<?php
// app/Models/Material.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'material_id';

    protected $fillable = [
        'name',
        'unit',
        'min_stock',
        'current_balance',
    ];

    public function appointments()
    {
        return $this->belongsToMany(
            Appointment::class,
            'appointment_materials',
            'material_id',
            'appointment_id'
        )->withPivot('quantity_used', 'cost_price', 'notes')
         ->withTimestamps();
    }

    public function services()
{
    return $this->belongsToMany(
        Service::class,
        'service_material',
        'material_id',
        'service_id'
    )->withPivot('quantity', 'is_required')
     ->withTimestamps();
}

    public function receipts()
    {
        return $this->hasMany(MaterialReceipt::class, 'material_id');
    }

    // Убираем или переделываем старую связь с consumption
    public function consumptions()
    {
        return $this->hasMany(Consumption::class, 'material_id');
    }
}