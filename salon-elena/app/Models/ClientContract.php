<?php
// app/Models/ClientContract.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContract extends Model
{
    protected $table = 'client_contracts';
    protected $primaryKey = 'contract_id';

    protected $fillable = [
        'contract_date',
        'status',
        'contract_number',
        'total_amount',
        'signed_at',
        'employee_id',
        'client_id',
        'appointment_id',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'signed_at' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'contract_id');
    }

    public function paymentToSuppliers()
    {
        return $this->hasMany(PaymentToSupplier::class, 'contract_id');
    }
}