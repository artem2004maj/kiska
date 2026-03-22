<?php
// app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'inn',
        'director_fio',
        'accountant_fio',
        'bank_name',
        'bic',
        'payment_account',
        'delivery_days',
    ];

    protected $casts = [
        'delivery_days' => 'integer',
    ];

    public function supplierContracts()
    {
        return $this->hasMany(SupplierContract::class, 'supplier_id', 'supplier_id');
    }
    
    // Связь с материалами, которые поставляет поставщик
    public function materials()
    {
        return $this->belongsToMany(
            Material::class,
            'supplier_materials',
            'supplier_id',
            'material_id'
        )->withPivot('price', 'is_active')
         ->withTimestamps();
    }
}