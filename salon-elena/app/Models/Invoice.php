<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'amount',
        'issued_at',
        'due_date',
        'paid_at',
        'method',
        'status',
        'contract_id',
        'appointment_id',
    ];

    public function clientContract()
    {
        return $this->belongsTo(ClientContract::class, 'contract_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}