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
        'price_per_unit',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
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
    public function suppliers()
    {
        return $this->belongsToMany(
            Supplier::class,
            'supplier_materials',
            'material_id',
            'supplier_id'
        )->withPivot('price', 'is_active')
         ->withTimestamps();
    }

    public function receipts()
    {
        return $this->hasMany(MaterialReceipt::class, 'material_id');
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class, 'material_id');
    }
}