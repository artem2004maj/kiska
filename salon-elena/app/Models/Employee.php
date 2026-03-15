<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees'; // если таблица называется employees

    protected $primaryKey = 'employee_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'employee_name',
        'role',
        'employee_phone',
        'email',        // ← было employee_email, стало email
        'login',
        'passwd',
    ];

    protected $hidden = [
        'passwd',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Для аутентификации используем email
    public function getAuthIdentifierName()
    {
        return 'email';  // ← меняем с login на email
    }

    public function getAuthPassword()
    {
        return $this->passwd;
    }

    public function getAuthIdentifier()
    {
        return $this->getKey(); // возвращает ID (число)
    }

    // Mutator для email (приводим к нижнему регистру)
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Связи (оставь как есть)
    public function clientContracts()
    {
        return $this->hasMany(ClientContract::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class);
    }
}