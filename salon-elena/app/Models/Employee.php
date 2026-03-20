<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage; // ДОБАВЛЕНО для работы с фото

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
        'photo', // ДОБАВЛЕНО
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
        return 'employee_id';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->employee_id;
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

    // ========== НОВЫЕ МЕТОДЫ ДЛЯ РАБОТЫ С ФОТО ==========
    
    /**
     * Аксессор для получения полного URL фото
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    /**
     * Проверка наличия фото
     */
    public function hasPhoto()
    {
        return !is_null($this->photo);
    }

    /**
     * Удаление фото
     */
    public function deletePhoto()
    {
        if ($this->photo) {
            Storage::disk('public')->delete($this->photo);
            $this->photo = null;
            $this->save();
        }
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'doctor_services', 'doctor_id', 'service_id')
                    ->withTimestamps();
    }
    // Связи
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