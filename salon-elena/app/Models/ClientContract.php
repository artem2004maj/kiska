<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContract extends Model
{
    protected $fillable = [
        'contract_date',
        'status',
        'contract_number',
        'total_amount',
        'signed_at',
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

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentToSuppliers() // если связь нужна
    {
        return $this->hasMany(PaymentToSupplier::class);
    }
}