<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_name',
        'role',
        'employee_phone',
        'email',
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

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'employee_id'; // Важно: возвращаем имя первичного ключа
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->employee_id; // Важно: возвращаем значение первичного ключа
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->passwd;
    }

    /**
     * Get the remember token for the user.
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the remember token for the user.
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the remember token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // Mutator для email
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Связи - указываем внешние ключи явно
    public function clientContracts()
    {
        return $this->hasMany(ClientContract::class, 'employee_id', 'employee_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'employee_id', 'employee_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'employee_id', 'employee_id');
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class, 'employee_id', 'employee_id');
    }
}