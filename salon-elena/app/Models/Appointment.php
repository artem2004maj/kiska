<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'date',
        'status',
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

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}