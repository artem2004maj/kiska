<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'inn',
        'director_fio',
        'accountant_fio',
        'bank_name',
        'bic',
        'payment_account',
        'delivery_days',
    ];

    public function supplierContracts()
    {
        return $this->hasMany(SupplierContract::class);
    }
}